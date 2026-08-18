<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_model extends CI_Model {

    protected $pgsql;

    public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->library('datatable_handler');
        try {
            $this->pgsql = $this->load->database('pgsql', TRUE);
        } catch (Exception $e) {
            log_message('error', 'PostgreSQL DB Connection Error: ' . $e->getMessage());
            $this->pgsql = null;
        }
    }

    private function _query_part_list($frame = null, $stockStatus = null) {
        $where_clauses = array("1=1");

        if (isset($frame) && !empty(trim($frame))) {
            $escaped_frame = $this->db->escape(trim($frame));
            $where_clauses[] = "cast(ff.frame as varchar(max)) = $escaped_frame";
        }

        $where_sql = implode(" AND ", $where_clauses);

        $having_clauses = array();
        if (isset($stockStatus) && !empty(trim($stockStatus))) {
            $status = trim($stockStatus);
            if ($status === 'ready') {
                $having_clauses[] = "(coalesce(max(x.qtyOnHand), 0) > 0 OR coalesce(max(avail.qtyAvailable), 0) > 0)";
            } else if ($status === 'empty') {
                $having_clauses[] = "coalesce(max(x.qtyOnHand), 0) <= 0 AND coalesce(max(avail.qtyAvailable), 0) <= 0";
            }
        }

        $having_sql = "";
        if (!empty($having_clauses)) {
            $having_sql = " HAVING " . implode(" AND ", $having_clauses);
        }

        $base_sql = "
            select cast(fpf.partInventoryCd as varchar(100)) as partCd, 
                max(cast(fpf.descr as varchar(max))) as partDesc, 
                max(cast(fpf.application as varchar(max))) as application,
                coalesce(max(x.qtyOnHand), 0) as qtyOnHand, 
                coalesce(max(avail.qtyAvailable), 0) as qtyAvailable,
                max(ii.baseUnit) as baseUnit,
                count(distinct ff.id) as frameCount,
                count(distinct cast(fpf.assemblySection as varchar(max))) as assemblyCount,
                max(cast(ff.frame as varchar(max))) as frame,
                max(cast(fpf.assemblySection as varchar(max))) as assemblySection
            from fmPartFrame as fpf
            inner join InventoryItem as ii on fpf.partInventoryId = ii.InventoryID and ii.CompanyID = 2
            left join fmFrame as ff on fpf.frameId = ff.id
            -- di left join karena ada unit yang belum masuk
            left join fmInventoryFrame as fif on ff.id = fif.frameId
            left join (
                select InventoryID, InventoryCD, InventoryName, sum(QtyOnhand) as qtyOnHand
                from db_fmm.dbo.tb_InventoryBalance
                where CompanyID = 2
                    and QtyOnHand > 0
                    and FinPeriodID = (
                        select max(FinPeriodID)
                        from db_fmm.dbo.tb_InventoryBalance
                        where CompanyID = 2
                    ) 
                group by InventoryID, InventoryCD, InventoryName
            ) as x on fpf.partInventoryId = x.InventoryID
            left join (
                select
                    a.InventoryID,
                    sum(a.QtyAvail - c.QtyPlan) as QtyAvailable
                from AcumaticaProduction_NEW.dbo.INSiteStatus as a
                left join AcumaticaProduction_NEW.dbo.ttvINSiteStatus as c on c.InventoryID = a.InventoryID 
                    and c.SiteID = a.SiteID 
                    and a.CompanyID = c.CompanyID
                where a.CompanyID = 2 and a.QtyAvail > 0
                group by a.InventoryID
            ) as avail on avail.InventoryID = ii.InventoryID
            where $where_sql
            group by cast(fpf.partInventoryCd as varchar(100))
            $having_sql
        ";

        return $base_sql;
    }

    public function get_part_list($frame = null, $stockStatus = null, $year = null) {
        if (empty($year)) {
            $year = date('Y');
        }

        $escapedYear = is_numeric($year) ? (int)$year : date('Y');

        // 1. Fetch PostgreSQL EPS summary for ALL products in the selected year
        $epsMappingTotal = array();
        $epsMappingPrice = array();

        try {
            if (!$this->pgsql) {
                $this->pgsql = $this->load->database('pgsql', TRUE);
            }

            $sqlEPSAll = "
                SELECT 
                    p.product_code,
                    COUNT(DISTINCT q.quotation_no_manual) as total,
                    SUM(qd.quotation_price * qd.qty) as total_price
                FROM quotation_details qd
                JOIN quotations q ON q.id = qd.quotation_id
                JOIN products p ON p.id = qd.product_id
                WHERE q.row_status = 0 
                    AND q.transaction_year = '$escapedYear'
                    AND q.quotation_status_id in (1, 2, 3, 4)
                    AND q.employee_id is not null
                    AND q.proportion_id is null
                GROUP BY p.product_code
            ";

            $resultEPSAll = $this->pgsql->query($sqlEPSAll)->result();

            foreach ($resultEPSAll as $eps) {
                $pCode = trim($eps->product_code);
                if ($pCode !== '') {
                    $epsMappingTotal[$pCode] = (int)$eps->total;
                    $epsMappingPrice[$pCode] = (float)$eps->total_price;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'PostgreSQL Query Error: ' . $e->getMessage());
        }

        // 2. Check DataTables ordering parameters
        $requestData = $this->input->post();
        $col_idx = isset($requestData['order']['0']['column']) ? (int)$requestData['order']['0']['column'] : -1;
        $order_dir = (isset($requestData['order']['0']['dir']) && strtoupper($requestData['order']['0']['dir']) === 'DESC') ? 'DESC' : 'ASC';

        $base_sql = $this->_query_part_list($frame, $stockStatus);
        $searchable_columns = array('partCd', 'partDesc', 'assemblySection', 'application', 'frame', 'qtyOnHand', 'qtyAvailable');
        
        $column_order = array(
            0 => 'partCd', 
            1 => 'partDesc', 
            2 => 'frame', 
            3 => 'assemblySection', 
            4 => 'application', 
            5 => 'qtyOnHand', 
            6 => 'qtyAvailable',
            7 => null, // TotalPenawaranEPS
            8 => null  // TotalPenawaranPrice
        );

        $default_sort = "ORDER BY partCd ASC";

        // If user wants to sort by TotalPenawaranEPS (col 7) or TotalPenawaranPrice (col 8)
        if ($col_idx === 7 || $col_idx === 8) {
            $cases = array();
            $targetMapping = ($col_idx === 7) ? $epsMappingTotal : $epsMappingPrice;

            foreach ($targetMapping as $code => $val) {
                if ($val > 0) {
                    $cleanCode = str_replace("'", "''", $code);
                    $cases[] = "WHEN '$cleanCode' THEN $val";
                }
            }

            if (!empty($cases)) {
                $case_expression = "CASE [partCd] " . implode(" ", $cases) . " ELSE 0 END";
                $default_sort = "ORDER BY " . $case_expression . " " . $order_dir . ", [partCd] ASC";
            } else {
                $default_sort = "ORDER BY [partCd] ASC";
            }
        }

        $result = $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);

        // 3. Attach EPS Total & Price to the paginated result rows
        if (!empty($result['data'])) {
            foreach ($result['data'] as $key => $row) {
                $inventoryCd = is_array($row) 
                    ? (isset($row['partCd']) ? trim($row['partCd']) : (isset($row['InventoryCD']) ? trim($row['InventoryCD']) : '')) 
                    : (isset($row->partCd) ? trim($row->partCd) : (isset($row->InventoryCD) ? trim($row->InventoryCD) : ''));

                $tot = isset($epsMappingTotal[$inventoryCd]) ? $epsMappingTotal[$inventoryCd] : 0;
                $prc = isset($epsMappingPrice[$inventoryCd]) ? $epsMappingPrice[$inventoryCd] : 0;

                if (is_array($result['data'][$key])) {
                    $result['data'][$key]['TotalPenawaranEPS'] = $tot;
                    $result['data'][$key]['TotalPenawaranPrice'] = $prc;
                } else {
                    $result['data'][$key]->TotalPenawaranEPS = $tot;
                    $result['data'][$key]->TotalPenawaranPrice = $prc;
                }
            }
        }

        return $result;
    }

    public function get_part_frames() {
        $sql = "
            select distinct cast(ff.frame as varchar(max)) as frame
            from fmFrame as ff
            inner join fmPartFrame as fpf on ff.id = fpf.frameId
            where ff.frame is not null and rtrim(ltrim(cast(ff.frame as varchar(max)))) <> ''
            order by cast(ff.frame as varchar(max)) asc
        ";
        return $this->db->query($sql)->result_array();
    }

    private function _query_populasi_unit($partCd = null, $lubricantCd = null, $branch = null) {
        $where = "";
        
        if (isset($lubricantCd) && !empty($lubricantCd)) {
            $subquery = "
                select fif.inventoryId
                from AcumaticaProduction_NEW.dbo.fmInventoryFrame as fif
                inner join AcumaticaProduction_NEW.dbo.fmFrame as ff on fif.frameId = ff.id
                inner join AcumaticaProduction_NEW.dbo.fmLubricantFrame as flf on cast(flf.producType as varchar(max)) = cast(ff.tipeProduct as varchar(max))
                where cast(flf.ccn as varchar(max)) = '$lubricantCd'
            ";
        } else {
            $inner_where = "";
            if (isset($partCd) && !empty($partCd)) {
                $inner_where .= " and cast(fpf.PartInventoryCD as varchar(max)) = '$partCd'"; 
            }
            $subquery = "
                select fif.inventoryId
                from AcumaticaProduction_NEW.dbo.fmInventoryFrame as fif
                inner join AcumaticaProduction_NEW.dbo.fmFrame as ff on fif.frameId = ff.id
                inner join AcumaticaProduction_NEW.dbo.fmPartFrame as fpf on ff.id = fpf.frameId
                where 1=1 $inner_where
            ";
        }

        if (isset($branch) && !empty($branch)) {
            $where .= " and BranchCD = '$branch'";
        }

        $base_sql = "
            select a.MasterUnitID, a.CustomerID, b.CustomerName, b.CustomerCode, 
                BranchCD, a.InventoryClassID, c.InventoryClassCode, c.InventoryClassName, 
                a.InventoryID, ii.InventoryCD, a.InventoryName, a.SerialNumber, d.HoursMeter
            from FMMService.dbo.MasterUnit a
            inner join (
                select max(MasterUnitID) as MasterUnitID, SerialNumber
                from FMMService.dbo.MasterUnit ia
                group by SerialNumber
            ) as a2 on a.MasterUnitID = a2.MasterUnitID
            inner join AcumaticaProduction_NEW.dbo.Branch as br on a.BranchID = br.BranchID 
            inner join AcumaticaProduction_NEW.dbo.InventoryItem as ii on a.InventoryID = ii.InventoryID 
                and br.CompanyID = ii.CompanyID
            left join FMMService.dbo.Customer b ON a.CustomerID = b.CustomerID
            left join FMMService.dbo.InventoryClass c ON a.InventoryClassID = c.InventoryClassID
            left join FMMService.dbo.MasterUnitHM d ON a.MasterUnitID = d.MasterUnitID
            where RowStatus = 1 and IsActive = 1 and br.CompanyID = 2 and a.InventoryID in (
                $subquery
            ) and nullif(rtrim(ltrim(a.SerialNumber)), '') is not null $where
        ";

        return $base_sql;
    }

    private function _query_populasi_unit_all($branch = null) {
        $where = "";

        if (isset($branch)) {
            $where .= "and br.BranchCD = '$branch'";
        }

        $query = "
            select b.CustomerName, b.CustomerCode, 
                br.BranchCD, a.InventoryClassID, ii.InventoryCD, 
                a.SerialNumber
            from FMMService.dbo.MasterUnit a
            inner join (
                select max(MasterUnitID) as MasterUnitID, SerialNumber
                from FMMService.dbo.MasterUnit ia
                group by SerialNumber
            ) as a2 on a.MasterUnitID = a2.MasterUnitID
            inner join AcumaticaProduction_NEW.dbo.Branch as br on a.BranchID = br.BranchID
            left join AcumaticaProduction_NEW.dbo.InventoryItem as ii on a.InventoryID = ii.InventoryID 
                and br.CompanyID = ii.CompanyID
            left join FMMService.dbo.Customer b ON a.CustomerID = b.CustomerID
            where RowStatus = 1 
                and IsActive = 1 
                and br.CompanyID = 2 
                and nullif(rtrim(ltrim(a.SerialNumber)), '') is not null
                and a.WorkGroupID = 1
                $where
        ";

        return $query;
    }

    public function get_populasi_unit($partCd) {
        $query = $this->_query_populasi_unit($partCd);
        $result = $this->db->query($query)->result();

        return $result;
    }

    public function get_populasi_unit_by_branch($branch) {
        $base_query = $this->_query_populasi_unit_all($branch);
        $query = "
            select *
            from (
                $base_query
            ) as x
            order by CustomerName, InventoryCD, SerialNumber
        ";
        $result = $this->db->query($query)->result_array();

        return $result;
    }

    public function get_populasi_unit_by_lubricant($lubricantCd) {
        $query = $this->_query_populasi_unit(null, $lubricantCd);
        $result = $this->db->query($query)->result_array();

        return $result;
    }

    private function _query_warehouse_stock() {
        $base_sql = "
            select inventoryCD,
                inventoryName, 
                baseUnit, 
                frameCount,
                frame,
                frameId,
                itemType,
                sum(qtyOnHand) as qtyOnHand,
                sum(qtyAvailable) as qtyAvailable,
                aging,
                salesPrice
            from (
                select v.inventoryCD, 
                    max(rtrim(ltrim(v.inventoryName))) as inventoryName, 
                    max(v.baseUnit) as baseUnit,
                    count(distinct v.frameId) as frameCount,
                    max(v.frame) as frame,
                    max(v.frameId) as frameId,
                    max(right(iic.descr, 4)) as itemType, 
                    coalesce(v.qtyOnHand, 0) as qtyOnHand, 
                    coalesce(avail.qtyAvailable, 0) as qtyAvailable,
                    max(v.aging) as aging, 
                    max(c.SalesPrice) as salesPrice
                from (
                    -- unit
                    select distinct siteId, ii.inventoryID, ii.inventoryCD, z.inventoryName, 
                        ii.ItemClassId, ii.baseUnit, cast(ff.frame as varchar(max)) as frame, ff.id as frameId, 
                        z.qtyOnHand, z.aging, ii.companyID
                    from InventoryItem as ii
                    left join fmInventoryFrame as fif on ii.inventoryID = fif.inventoryID
                    left join fmFrame as ff on fif.frameId = ff.id
                    inner join (
                        select siteId, inventoryID, inventoryCD, InventoryName, 
                            sum(QtyOnHand) as QtyOnHand, datediff(day, LastReceiptDate, getdate()) as Aging
                        from db_fmm.dbo.tb_InventoryBalance
                        where CompanyID = 2
                            and QtyOnHand > 0
                            and FinPeriodID = (
                                select max(FinPeriodID)
                                from db_fmm.dbo.tb_InventoryBalance
                            )
                        group by siteId, inventoryID, inventoryCD, InventoryName, LastReceiptDate
                    ) as z on fif.inventoryID = z.inventoryID
                    where ii.CompanyID = 2
                    union
                    -- part
                    select distinct siteId, ii.inventoryID, ii.inventoryCD, z.inventoryName, 
                        ii.itemClassId, ii.baseUnit, cast(ff.frame as varchar(max)) as frame, ff.id as frameId, 
                        z.qtyOnHand, z.aging, ii.companyID
                    from InventoryItem as ii
                    left join fmPartFrame as fpf on ii.inventoryID = fpf.partInventoryID
                    left join fmFrame as ff on fpf.frameId = ff.id
                    inner join (
                        select siteId, inventoryID, inventoryCD, InventoryName, 
                            sum(QtyOnHand) as QtyOnHand, datediff(day, LastReceiptDate, getdate()) as Aging
                        from db_fmm.dbo.tb_InventoryBalance
                        where CompanyID = 2
                            and QtyOnHand > 0
                            and FinPeriodID = (
                                select max(FinPeriodID)
                                from db_fmm.dbo.tb_InventoryBalance
                            )
                        group by siteId, inventoryID, inventoryCD, inventoryName, lastReceiptDate
                    ) as z on fpf.partInventoryID = z.inventoryID
                    where ii.CompanyID = 2
                ) as v
                inner join ARSalesPrice as c on v.inventoryID = c.inventoryID 
                    and v.CompanyID = c.CompanyID
                inner join INItemClass as iic on v.itemClassId = iic.itemClassId 
                    and v.CompanyID = iic.CompanyID
                left join (
                    select
                        a.SiteID,
                        a.CompanyID,
                        a.InventoryID,
                        b.InventoryCD,
                        sum(a.QtyAvail - c.QtyPlan) as QtyAvailable
                    from AcumaticaProduction_NEW.dbo.INSiteStatus as a
                    join AcumaticaProduction_NEW.dbo.InventoryItem as b on b.InventoryID = a.InventoryID 
                        and a.CompanyID = b.CompanyID
                    left join AcumaticaProduction_NEW.dbo.ttvINSiteStatus as c on c.InventoryID = a.InventoryID 
                        and c.SiteID = a.SiteID 
                        and a.CompanyID = c.CompanyID
                    where a.CompanyID = 2 and a.QtyAvail > 0 and a.SiteID != 1
                    group by a.SiteID, a.CompanyID, a.InventoryID, b.InventoryCD
                ) as avail on avail.InventoryID = v.InventoryID 
                    and avail.SiteID = v.SiteID
                group by v.inventoryCD, coalesce(v.qtyOnHand, 0), coalesce(avail.qtyAvailable, 0)
            ) as x
            group by InventoryCD,
                inventoryName, 
                baseUnit, 
                frameCount,
                frame,
                frameId,
                itemType,
                aging,
                salesPrice
        ";

        return $base_sql;
    }

    public function get_warehouse_stock() {
        $base_sql = $this->_query_warehouse_stock();

        $searchable_columns = array('inventoryCD', 'inventoryName', 'itemType', 'frame', 'aging', 'qtyOnHand', 'qtyAvailable', 'salesPrice');
        $column_order = array('inventoryCD', 'inventoryName', 'itemType', 'frame', 'aging', 'qtyOnHand', 'qtyAvailable', 'salesPrice');
        $default_sort = "ORDER BY inventoryCD ASC";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }

    private function _query_sparepart_sales() {
        $base_sql = "
            select rtrim(ltrim(inventoryCD)) as inventoryCD, inventoryName, 
                fourYearAgoSold, threeYearAgoSold, twoYearAgoSold, oneYearAgoSold, currentSold, 
                (fourYearAgoSold + threeYearAgoSold + twoYearAgoSold + oneYearAgoSold + currentSold) as totalSold, qtyOnHand, qtyAvailable 
                --, (case when (twoYearAgoSold + oneYearAgoSold + currentSold) / 3.0 > 0 
                --    then (qtyOnHand / ((twoYearAgoSold + oneYearAgoSold + currentSold) / 3.0)) else 0 end) as rasioYear
            from (
                select distinct tib.inventoryCD, tib.inventoryName, 
                    sum((case when year(trandate) = year(getdate()) - 4 then tbs.qty else 0 end)) as fourYearAgoSold,
                    sum((case when year(trandate) = year(getdate()) - 3 then tbs.qty else 0 end)) as threeYearAgoSold,
                    sum((case when year(trandate) = year(getdate()) - 2 then tbs.qty else 0 end)) as twoYearAgoSold,
                    sum((case when year(trandate) = year(getdate()) - 1 then tbs.qty else 0 end)) as oneYearAgoSold,
                    sum((case when year(trandate) = year(getdate()) then tbs.qty else 0 end)) as currentSold,
                    tib.qtyOnHand,
                    coalesce(max(avail.qtyAvailable), 0) as qtyAvailable
                from db_fmm.dbo.tb_stagging as tbs
                inner join (
                    select InventoryID, InventoryCD, InventoryName, sum(QtyOnHand) as QtyOnHand
                    from db_fmm.dbo.tb_InventoryBalance
                    where CompanyID = 2
                        and QtyOnHand > 0
                        and FinPeriodID = (
                            select max(FinPeriodID)
                            from db_fmm.dbo.tb_InventoryBalance
                        )
                    group by InventoryID, InventoryCD, InventoryName
                ) as tib on tbs.InventoryID = tib.InventoryID
                left join (
                    select
                        a.InventoryID,
                        sum(a.QtyAvail - c.QtyPlan) as QtyAvailable
                    from AcumaticaProduction_NEW.dbo.INSiteStatus as a
                    left join AcumaticaProduction_NEW.dbo.ttvINSiteStatus as c on c.InventoryID = a.InventoryID 
                        and c.SiteID = a.SiteID 
                        and a.CompanyID = c.CompanyID
                    where a.CompanyID = 2 and a.QtyAvail > 0
                    group by a.InventoryID
                ) as avail on avail.InventoryID = tib.InventoryID
                where exists (
                    select 1
                    from AcumaticaProduction_NEW.dbo.fmPartFrame as fpf
                    where fpf.partInventoryId = tib.inventoryId
                )
                group by tib.inventoryCD, tib.inventoryName, tib.qtyOnHand
            ) as cons
        ";

        return $base_sql;
    }

    public function get_sparepart_sales() {
        $base_sql = $this->_query_sparepart_sales();

        $searchable_columns = array('inventoryCD', 'inventoryName',
            'fourYearAgoSold', 'threeYearAgoSold',
            'twoYearAgoSold', 'oneYearAgoSold', 
            'currentSold', 'totalSold', 'qtyOnHand');
        $column_order = array('inventoryCD', 'inventoryName', 
            'fourYearAgoSold', 'threeYearAgoSold',
            'twoYearAgoSold', 'oneYearAgoSold', 
            'currentSold', 'totalSold', 'qtyOnHand');
        $default_sort = "order by inventoryCD asc";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }

    private function _query_top_customers($inventoryCd) {
        $base_sql = "
            select customerName, branchCD, sum(qty) as qty, max(tranDate) as tranDate 
            from db_fmm.dbo.tb_stagging as tbs
            where rtrim(ltrim(tbs.inventoryCD)) = '$inventoryCd'
            group by customerName, branchCD
            order by sum(qty) desc
        ";

        return $base_sql;
    }

    public function get_top_customers($inventoryCd) {
        $base_sql = $this->_query_top_customers($inventoryCd);
        $result = $this->db->query($base_sql)->result_array();

        return $result;
    }

    public function get_unit_distribution() {
        $base_sql = $this->_query_populasi_unit_all();
        $query = "
            select ltrim(rtrim(BranchCD)) as BranchCD, count(distinct SerialNumber) as CountSerialNumber, 
                count(distinct CustomerCode) as CountCustomerCode
            from ($base_sql) as x
            group by ltrim(rtrim(BranchCD))
        ";

        $result = $this->db->query($query)->result_array();

        return $result;
    }

    private function _query_customers_by_part($part) {
        $base_sql = "
            select distinct 
                b.CustomerName, 
                b.CustomerCode, 
                br.BranchCD, 
                cast(ff.frame as varchar(max)) as frame, 
                a.SerialNumber,
                cast(fpf.partInventoryCd as varchar(max)) as partInventoryCd,
                cast(fpf.descr as varchar(max)) as partDesc,
                isnull(part_stock.qtyOnHand, 0) as qtyOnHand
            from FMMService.dbo.MasterUnit a
            inner join AcumaticaProduction_NEW.dbo.Branch as br on a.BranchID = br.BranchID 
            inner join AcumaticaProduction_NEW.dbo.InventoryItem as ii on a.InventoryID = ii.InventoryID 
                and br.CompanyID = ii.CompanyID
            inner join AcumaticaProduction_NEW.dbo.fmInventoryFrame as fif on ii.InventoryID = fif.InventoryID
            inner join AcumaticaProduction_NEW.dbo.fmFrame as ff on fif.frameId = ff.id
            inner join AcumaticaProduction_NEW.dbo.fmPartFrame as fpf on ff.id = fpf.frameId
            left join FMMService.dbo.Customer b ON a.CustomerID = b.CustomerID
            left join (
                select InventoryID, sum(QtyOnhand) as qtyOnHand
                from db_fmm.dbo.tb_InventoryBalance
                where CompanyID = 2
                  and FinPeriodID = (
                      select max(FinPeriodID)
                      from db_fmm.dbo.tb_InventoryBalance
                  )
                group by InventoryID
            ) as part_stock on fpf.partInventoryId = part_stock.InventoryID
            where RowStatus = 1 and IsActive = 1 and br.CompanyID = 2 and a.InventoryID in (
                select fif.inventoryId
                from AcumaticaProduction_NEW.dbo.fmInventoryFrame as fif
                inner join AcumaticaProduction_NEW.dbo.fmFrame as ff on fif.frameId = ff.id
            ) and (cast(fpf.partInventoryCd as varchar(max)) like '%$part%' or cast(fpf.descr as varchar(max)) like '%$part%')
        ";

        return $base_sql;
    }

    public function get_customers_by_part($part) {
        $base_sql = $this->_query_customers_by_part($part);
        $result = $this->db->query($base_sql)->result_array();

        return $result;
    }

    public function get_part_frames_assemblies($partCd) {
        $sql = "
            select distinct cast(ff.frame as varchar(max)) as frame, 
                   cast(fpf.assemblySection as varchar(max)) as assemblySection
            from fmPartFrame as fpf
            left join fmFrame as ff on fpf.frameId = ff.id
            where cast(fpf.partInventoryCd as varchar(100)) = ?
        ";

        return $this->db->query($sql, array($partCd))->result_array();
    }

    private function _query_airend_rotary($region = null, $category = null) {
        $where_clauses = array("1=1");

        if (isset($region) && !empty(trim($region))) {
            $escaped_region = $this->db->escape(trim($region));
            $where_clauses[] = "cast(faf.region as varchar(100)) = $escaped_region";
        }

        if (isset($category) && !empty(trim($category))) {
            $escaped_category = $this->db->escape(trim($category));
            $where_clauses[] = "cast(faf.category as varchar(100)) = $escaped_category";
        }

        $where_sql = implode(" AND ", $where_clauses);

        $sql = "
            select cast(faf.region as varchar(100)) as region, 
                cast(faf.category as varchar(100)) as category, 
                cast(faf.model as varchar(255)) as model, 
                cast(faf.identitySize as varchar(255)) as identitySize, 
                cast(faf.factoryRebuiltCcn as varchar(100)) as factoryRebuiltCcn,
                faf.factoryRebuiltApdc,
                cast(faf.factoryRebuiltBackup as varchar(255)) as factoryRebuiltBackup,
                (case when tib1.inventoryCd is not null then tib1.qtyOnHand else 0 end) as frbQtyOnHand,
                cast(faf.newAirendCcn as varchar(100)) as newAirendCcn,
                (case when tib2.inventoryCd is not null then tib2.qtyOnHand else 0 end) as naQtyOnHand,
                cast(faf.rebuiltKitAirend as varchar(100)) as rebuiltKitAirendCcn,
                faf.rebuiltKitAirendApdc,
                (case when tib3.inventoryCd is not null then tib3.qtyOnHand else 0 end) as rkaQtyOnHand
            from fmAirendFrame as faf
            left join (
                select InventoryCD, sum(QtyOnhand) as qtyOnHand
                from db_fmm.dbo.tb_InventoryBalance
                where CompanyID = 2
                    and FinPeriodID = (
                        select max(FinPeriodID)
                        from db_fmm.dbo.tb_InventoryBalance
                        where CompanyID = 2
                    )
                group by InventoryCD
            ) as tib1 on cast(faf.factoryRebuiltCcn as varchar(100)) = tib1.InventoryCd
            left join (
                select InventoryCD, sum(QtyOnhand) as qtyOnHand
                from db_fmm.dbo.tb_InventoryBalance
                where CompanyID = 2
                    and FinPeriodID = (
                        select max(FinPeriodID)
                        from db_fmm.dbo.tb_InventoryBalance
                        where CompanyID = 2
                    )
                group by InventoryCD
            ) as tib2 on cast(faf.newAirendCcn as varchar(100)) = tib2.InventoryCd
            left join (
                select InventoryCD, sum(QtyOnhand) as qtyOnHand
                from db_fmm.dbo.tb_InventoryBalance
                where CompanyID = 2
                    and FinPeriodID = (
                        select max(FinPeriodID)
                        from db_fmm.dbo.tb_InventoryBalance
                        where CompanyID = 2
                    )
                group by InventoryCD
            ) as tib3 on cast(faf.rebuiltKitAirend as varchar(100)) = tib3.InventoryCd
            where $where_sql
        ";

        return $sql;
    }

    public function get_airend_rotary($region = null, $category = null) {
        $base_sql = $this->_query_airend_rotary($region, $category);

        $searchable_columns = array(
            'region', 'category', 'model', 'identitySize', 
            'factoryRebuiltCcn', 'newAirendCcn', 'rebuiltKitAirendCcn'
        );
        $column_order = array(
            'region', 'category', 'model', 'identitySize', 
            'factoryRebuiltCcn', 'newAirendCcn', 'rebuiltKitAirendCcn'
        );
        $default_sort = "ORDER BY model ASC";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }

    public function get_airend_rotary_filter_options() {
        $regions = $this->db->query("select distinct cast(region as varchar(100)) as region from fmAirendFrame where region is not null and cast(region as varchar(100)) != '' order by region asc")->result_array();
        $categories = $this->db->query("select distinct cast(category as varchar(100)) as category from fmAirendFrame where category is not null and cast(category as varchar(100)) != '' order by category asc")->result_array();
        
        return array(
            'regions' => array_column($regions, 'region'),
            'categories' => array_column($categories, 'category')
        );
    }

    public function get_lubricant_coolant_filter_options() {
        $categories = $this->db->query("select distinct cast(category as varchar(100)) as category from fmLubricantFrame where category is not null and cast(category as varchar(100)) != '' order by category asc")->result_array();
        $frames = $this->db->query("select distinct cast(ff.frame as varchar(100)) as frame from fmLubricantFrame as flf left join fmFrame as ff on cast(flf.producType as varchar(max)) = cast(ff.tipeProduct as varchar(max)) where ff.frame is not null and cast(ff.frame as varchar(100)) != '' order by frame asc")->result_array();
        
        return array(
            'categories' => array_column($categories, 'category'),
            'frames' => array_column($frames, 'frame')
        );
    }

    private function _query_lubricant_coolant($category = null, $frame = null) {
        $where_clauses = array("1=1");

        if (isset($category) && !empty(trim($category))) {
            $escaped_category = $this->db->escape(trim($category));
            $where_clauses[] = "cast(flf.category as varchar(100)) = $escaped_category";
        }

        if (isset($frame) && !empty(trim($frame))) {
            $escaped_frame = $this->db->escape(trim($frame));
            $where_clauses[] = "cast(ff.frame as varchar(100)) = $escaped_frame";
        }

        $where_sql = implode(" AND ", $where_clauses);

        $sql = "
            select ccn, 
                description, 
                category, 
                baseStock, 
                serviceLife, 
                containerSize, 
                containerType, 
                applicationUsed, 
                isovg, 
                frameCount, 
                frame, 
                sum(qtyOnHand) as qtyOnHand, 
                sum(qtyAvailable) as qtyAvailable
            from (
                select cast(flf.ccn as varchar(max)) as ccn,
                    max(cast(flf.description as varchar(max))) as description,
                    max(cast(flf.category as varchar(max))) as category,
                    max(cast(flf.baseStock as varchar(max))) as baseStock,
                    max(cast(flf.serviceLife as varchar(max))) as serviceLife,
                    max(cast(flf.containerSize as varchar(max))) as containerSize,
                    max(cast(flf.containerType as varchar(max))) as containerType,
                    max(cast(flf.applicationUsed as varchar(max))) as applicationUsed,
                    max(cast(flf.isovg as varchar(max))) as isovg,
                    count(distinct cast(ff.frame as varchar(max))) as frameCount,
                    max(cast(ff.frame as varchar(max))) as frame,
                    coalesce(tib.qtyOnHand, 0) as qtyOnHand,
                    coalesce(avail.qtyAvailable, 0) as qtyAvailable
                from fmLubricantFrame as flf
                inner join (
                    select SiteID, InventoryID, InventoryCD, InventoryName, sum(QtyOnHand) as QtyOnHand
                    from db_fmm.dbo.tb_InventoryBalance
                    where CompanyID = 2
                        and QtyOnHand > 0
                        and FinPeriodID = (
                            select max(FinPeriodID)
                            from db_fmm.dbo.tb_InventoryBalance
                            where CompanyID = 2
                        )
                    group by SiteID, InventoryID, InventoryCD, InventoryName
                ) as tib on cast(flf.ccn as varchar(max)) = tib.InventoryCD
                left join (
                    select
                        a.SiteID,
                        a.CompanyID,
                        a.InventoryID,
                        sum(a.QtyAvail - c.QtyPlan) as QtyAvailable
                    from AcumaticaProduction_NEW.dbo.INSiteStatus as a
                    left join AcumaticaProduction_NEW.dbo.ttvINSiteStatus as c on c.InventoryID = a.InventoryID 
                        and c.SiteID = a.SiteID 
                        and a.CompanyID = c.CompanyID
                    where a.CompanyID = 2 
                        and a.QtyAvail > 0 
                        and a.SiteID != 1
                    group by a.SiteID,
                        a.CompanyID,
                        a.InventoryID
                ) as avail on avail.InventoryID = tib.InventoryID and avail.SiteID = tib.SiteID
                left join fmFrame as ff on cast(flf.producType as varchar(max)) = cast(ff.tipeProduct as varchar(max))
                where $where_sql
                group by cast(flf.ccn as varchar(max)), coalesce(tib.qtyOnHand, 0), coalesce(avail.qtyAvailable, 0)
            ) as x 
            group by ccn, 
                description, 
                category, 
                baseStock, 
                serviceLife, 
                containerSize, 
                containerType, 
                applicationUsed, 
                isovg, 
                frameCount, 
                frame
        ";

        return $sql;
    }

    public function get_lubricant_coolant($category = null, $frame = null) {
        $base_sql = $this->_query_lubricant_coolant($category, $frame);
        $searchable_columns = array(
            'ccn', 'description', 'category', 'frame', 'containerSize', 'containerType', 'applicationUsed', 'qtyOnHand', 'qtyAvailable'
        );
        $column_order = array(
            'ccn', 'description', 'frame', 'category', 'containerSize', 'qtyOnHand', 'qtyAvailable'
        );
        $default_sort = "ORDER BY ccn ASC";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }

    public function get_lubricant_details($ccn) {
        $sql = "
            select distinct cast(ff.frame as varchar(max)) as frame
            from fmLubricantFrame as flf
            left join fmFrame as ff on cast(flf.producType as varchar(max)) = cast(ff.tipeProduct as varchar(max))
            where cast(flf.ccn as varchar(max)) = ?
        ";

        return $this->db->query($sql, array($ccn))->result_array();
    }

    public function get_centac_filter_options() {
        $customers = $this->db->query("select distinct cast(customerName as varchar(255)) as customerName from AcumaticaProduction_NEW.dbo.fmCentrifugalFrame where customerName is not null and cast(customerName as varchar(255)) != '' order by customerName asc")->result_array();
        $models = $this->db->query("select distinct cast(unitInventoryCd as varchar(255)) as unitInventoryCd from AcumaticaProduction_NEW.dbo.fmCentrifugalFrame where unitInventoryCd is not null and cast(unitInventoryCd as varchar(255)) != '' order by unitInventoryCd asc")->result_array();
        
        return array(
            'customers' => array_column($customers, 'customerName'),
            'models' => array_column($models, 'unitInventoryCd')
        );
    }

    private function _query_centac($customer = null, $model = null, $stockStatus = null) {
        $where_clauses = array("1=1");

        if (isset($customer) && !empty(trim($customer))) {
            $escaped_cust = $this->db->escape(trim($customer));
            $where_clauses[] = "cast(fcf.customerName as varchar(255)) = $escaped_cust";
        }

        if (isset($model) && !empty(trim($model))) {
            $escaped_model = $this->db->escape(trim($model));
            $where_clauses[] = "cast(fcf.unitInventoryCd as varchar(255)) = $escaped_model";
        }

        if (isset($stockStatus) && !empty(trim($stockStatus))) {
            $status = trim($stockStatus);
            if ($status === 'ready') {
                $where_clauses[] = "isnull(tib.QtyOnHand, 0) > 0";
            } else if ($status === 'empty') {
                $where_clauses[] = "isnull(tib.QtyOnHand, 0) <= 0";
            }
        }

        $where_sql = implode(" AND ", $where_clauses);

        $sql = "
            select 
                cast(fcf.customerName as varchar(max)) as customerName, 
                cast(fcf.unitInventoryCd as varchar(max)) as unitInventoryCd, 
                cast(fcf.unitSerialNumber as varchar(max)) as unitSerialNumber, 
                cast(fcf.partInventoryCd as varchar(max)) as partInventoryCd, 
                cast(fcf.partDescription as varchar(max)) as partDescription, 
                cast(fcf.partQty as varchar(max)) as partQty, 
                cast(fcf.partUom as varchar(max)) as partUom, 
                cast(fcf.reference as varchar(max)) as reference, 
                cast(fcf.application as varchar(max)) as application, 
                isnull(tib.QtyOnHand, 0) as qtyOnHand
            from AcumaticaProduction_NEW.dbo.fmCentrifugalFrame as fcf
            left join (
                select InventoryID, InventoryCD, InventoryName, sum(QtyOnHand) as QtyOnHand
                from db_fmm.dbo.tb_InventoryBalance
                where CompanyID = 2
                    and QtyOnHand > 0
                    and FinPeriodID = (
                        select max(FinPeriodID)
                        from db_fmm.dbo.tb_InventoryBalance
                        where CompanyID = 2
                    )
                group by InventoryID, InventoryCD, InventoryName
            ) as tib on cast(fcf.partInventoryCd as varchar(max)) = tib.InventoryCD
            where $where_sql
        ";

        return $sql;
    }

    public function get_centac($customer = null, $model = null, $stockStatus = null) {
        $base_sql = $this->_query_centac($customer, $model, $stockStatus);
        $searchable_columns = array(
            'customerName', 'unitInventoryCd', 'unitSerialNumber', 
            'partInventoryCd', 'partDescription', 'reference', 'application'
        );
        $column_order = array(
            'customerName', 'partInventoryCd', 'partDescription', 
            'partQty', 'reference', 'application', 'qtyOnHand'
        );
        $default_sort = "ORDER BY customerName ASC";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }
}
