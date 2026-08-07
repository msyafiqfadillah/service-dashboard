<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->library('datatable_handler');
    }

    private function _query_part_list() {
        $base_sql = "
            select cast(fpf.partInventoryCd as varchar(100)) as partCd, 
                max(cast(fpf.descr as varchar(max))) as partDesc, 
                max(cast(fpf.application as varchar(max))) as application,
                max(x.qtyOnHand) as qtyOnHand, 
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
            inner join (
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
            group by cast(fpf.partInventoryCd as varchar(100))
        ";

        return $base_sql;
    }

    public function get_part_list() {
        $base_sql = $this->_query_part_list();
        $searchable_columns = array('partCd', 'partDesc', 'assemblySection', 'application', 'frame', 'qtyOnHand');
        $column_order = array('partCd', 'partDesc', 'frame', 'assemblySection', 'application', 'qtyOnHand');
        $default_sort = "order by partCd ASC";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }

    private function _query_populasi_unit($inventoryCd = null, $branch = null) {
        $where = "";
        $inner_where = "";
        
        if (isset($inventoryCd)) {
            $inner_where .= " and cast(fpf.PartInventoryCD as varchar(max)) = '$inventoryCd'";
        }

        if (isset($branch)) {
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
                select fif.inventoryId
                from AcumaticaProduction_NEW.dbo.fmInventoryFrame as fif
                inner join AcumaticaProduction_NEW.dbo.fmFrame as ff on fif.frameId = ff.id
                inner join AcumaticaProduction_NEW.dbo.fmPartFrame as fpf on ff.id = fpf.frameId
                where 1=1 $inner_where
            ) and nullif(rtrim(ltrim(a.SerialNumber)), '') is not null $where
        ";

        return $base_sql;
    }

    public function get_populasi_unit($inventoryCd) {
        $query = $this->_query_populasi_unit($inventoryCd);
        $result = $this->db->query($query)->result();

        return $result;
    }

    public function get_populasi_unit_by_branch($branch) {
        $query = $this->_query_populasi_unit(null, $branch);
        $result = $this->db->query($query)->result_array();

        return $result;
    }

    private function _query_warehouse_stock() {
        $base_sql = "
            select v.inventoryCD, 
                max(rtrim(ltrim(v.inventoryName))) as inventoryName, 
                max(v.baseUnit) as baseUnit,
                count(distinct v.frameId) as frameCount,
                max(v.frame) as frame,
                max(v.frameId) as frameId,
                max(right(iic.descr, 4)) as itemType, 
                max(v.qtyOnHand) as qtyOnHand, 
                max(v.aging) as aging, 
                max(c.SalesPrice) as salesPrice
            from (
                -- unit
                select distinct ii.inventoryID, ii.inventoryCD, z.inventoryName, 
                    ii.ItemClassId, ii.baseUnit, cast(ff.frame as varchar(max)) as frame, ff.id as frameId, 
                    z.qtyOnHand, z.aging, ii.companyID
                from InventoryItem as ii
                left join fmInventoryFrame as fif on ii.inventoryID = fif.inventoryID
                left join fmFrame as ff on fif.frameId = ff.id
                inner join (
                    select inventoryID, inventoryCD, InventoryName, 
                        sum(QtyOnHand) as QtyOnHand, datediff(day, LastReceiptDate, getdate()) as Aging
                    from db_fmm.dbo.tb_InventoryBalance
                    where CompanyID = 2
                        and QtyOnHand > 0
                        and FinPeriodID = (
                            select max(FinPeriodID)
                            from db_fmm.dbo.tb_InventoryBalance
                        )
                    group by inventoryID, inventoryCD, InventoryName, LastReceiptDate
                ) as z on fif.inventoryID = z.inventoryID
                where ii.CompanyID = 2
                union
                -- part
                select distinct ii.inventoryID, ii.inventoryCD, z.inventoryName, 
                    ii.itemClassId, ii.baseUnit, cast(ff.frame as varchar(max)) as frame, ff.id as frameId, 
                    z.qtyOnHand, z.aging, ii.companyID
                from InventoryItem as ii
                left join fmPartFrame as fpf on ii.inventoryID = fpf.partInventoryID
                left join fmFrame as ff on fpf.frameId = ff.id
                inner join (
                    select inventoryID, inventoryCD, InventoryName, 
                        sum(QtyOnHand) as QtyOnHand, datediff(day, LastReceiptDate, getdate()) as Aging
                    from db_fmm.dbo.tb_InventoryBalance
                    where CompanyID = 2
                        and QtyOnHand > 0
                        and FinPeriodID = (
                            select max(FinPeriodID)
                            from db_fmm.dbo.tb_InventoryBalance
                        )
                    group by inventoryID, inventoryCD, InventoryName, LastReceiptDate
                ) as z on fpf.partInventoryID = z.inventoryID
                where ii.CompanyID = 2
            ) as v
            inner join ARSalesPrice as c on v.inventoryID = c.inventoryID 
                and v.CompanyID = c.CompanyID
            inner join INItemClass as iic on v.itemClassId = iic.itemClassId 
                and v.CompanyID = iic.CompanyID
            group by v.inventoryCD
        ";

        return $base_sql;
    }

    public function get_warehouse_stock() {
        $base_sql = $this->_query_warehouse_stock();

        $searchable_columns = array('inventoryCD', 'inventoryName', 'itemType', 'frame', 'aging', 'qtyOnHand', 'salesPrice');
        $column_order = array('inventoryCD', 'inventoryName', 'itemType', 'frame', 'aging', 'qtyOnHand', 'salesPrice');
        $default_sort = "ORDER BY inventoryCD ASC";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }

    private function _query_sparepart_sales() {
        $base_sql = "
            select rtrim(ltrim(inventoryCD)) as inventoryCD, inventoryName, 
                fourYearAgoSold, threeYearAgoSold, twoYearAgoSold, oneYearAgoSold, currentSold, 
                (fourYearAgoSold + threeYearAgoSold + twoYearAgoSold + oneYearAgoSold + currentSold) as totalSold, qtyOnHand 
                --, (case when (twoYearAgoSold + oneYearAgoSold + currentSold) / 3.0 > 0 
                --    then (qtyOnHand / ((twoYearAgoSold + oneYearAgoSold + currentSold) / 3.0)) else 0 end) as rasioYear
            from (
                select distinct tib.inventoryCD, tib.inventoryName, 
                    sum((case when year(trandate) = year(getdate()) - 4 then tbs.qty else 0 end)) as fourYearAgoSold,
                    sum((case when year(trandate) = year(getdate()) - 3 then tbs.qty else 0 end)) as threeYearAgoSold,
                    sum((case when year(trandate) = year(getdate()) - 2 then tbs.qty else 0 end)) as twoYearAgoSold,
                    sum((case when year(trandate) = year(getdate()) - 1 then tbs.qty else 0 end)) as oneYearAgoSold,
                    sum((case when year(trandate) = year(getdate()) then tbs.qty else 0 end)) as currentSold,
                    tib.qtyOnHand
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
        $base_sql = $this->_query_populasi_unit();
        $query = "
            select ltrim(rtrim(BranchCD)) as BranchCD, count(distinct SerialNumber) as CountSerialNumber, 
                count(distinct CustomerCode) as CountCustomerCode
            from ($base_sql) as x
            group by ltrim(rtrim(BranchCD))
        ";

        $result = $this->db->query($query)->result_array();

        return $result;
    }

    private function _query_customers_by_part() {
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
            ) and (cast(fpf.partInventoryCd as varchar(max)) like ? or cast(fpf.descr as varchar(max)) like ?)
        ";

        return $base_sql;
    }

    public function get_customers_by_part($part) {
        $base_sql = $this->_query_customers_by_part();
        $term = '%' . trim($part) . '%';
        $result = $this->db->query($base_sql, array($term, $term))->result_array();

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

    private function _query_airend_rotary() {
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
        ";

        return $sql;
    }

    public function get_airend_rotary() {
        $base_sql = $this->_query_airend_rotary();

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

    private function _query_lubricant_coolant() {
        $sql = "
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
                isnull(max(tib.qtyOnHand), 0) as qtyOnHand
            from fmLubricantFrame as flf
            inner join (
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
            ) as tib on cast(flf.ccn as varchar(max)) = tib.InventoryCD
            left join fmFrame as ff on cast(flf.producType as varchar(max)) = cast(ff.tipeProduct as varchar(max))
            group by cast(flf.ccn as varchar(max))
        ";

        return $sql;
    }

    public function get_lubricant_coolant() {
        $base_sql = $this->_query_lubricant_coolant();
        $searchable_columns = array(
            'ccn', 'description', 'category', 'frame', 'containerSize', 'containerType', 'applicationUsed'
        );
        $column_order = array(
            'ccn', 'frame', 'description', 'category', 'containerSize', 'qtyOnHand'
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
}
