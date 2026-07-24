<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
    }

    private function _query_part_list() {
        $base_sql = "
            select distinct fpf.partInventoryCd as partCd, cast(fpf.descr as varchar(max)) as partDesc, 
                cast(fpf.assemblySection as varchar(max)) as assemblySection, ff.id as frameId,
                cast(ff.frame as varchar(max)) as frame, cast(fpf.application as varchar(max)) as application,
                x.qtyOnHand, ii.baseUnit
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
                ) 
                group by InventoryID, InventoryCD, InventoryName
            ) as x on fpf.partInventoryId = x.InventoryID
        ";

        return $base_sql;
    }

    public function get_part_list() {
        $base_sql = $this->_query_part_list();
        $searchable_columns = array('partCd', 'partDesc', 'assemblySection', 'application', 'frame');
        $column_order = array('partCd', 'partDesc', 'frame', 'assemblySection', 'application');
        $default_sort = "order by partCd ASC";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }

    private function _query_populasi_unit($frameId) {
        $base_sql = "
            select a.MasterUnitID, a.CustomerID, b.CustomerName, b.CustomerCode, 
                BranchCD, a.InventoryClassID, c.InventoryClassCode, c.InventoryClassName, 
                a.InventoryID, ii.InventoryCD, a.InventoryName, a.SerialNumber, d.HoursMeter
            from FMMService.dbo.MasterUnit a
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
                where ff.id = $frameId
            )
        ";

        return $base_sql;
    }

    public function get_populasi_unit($frameId) {
        $query = $this->_query_populasi_unit($frameId);
        $result = $this->db->query($query)->result();

        return $result;
    }

    private function _query_warehouse_stock() {
        $base_sql = "
            select v.inventoryCD, v.inventoryName, v.baseUnit,
                v.frame, v.frameId, right(iic.descr, 4) as itemType, 
                v.qtyOnHand, v.aging, format(max(c.SalesPrice), 'N0') as salesPrice
            from (
                -- unit
                select distinct ii.inventoryID, ii.inventoryCD, z.inventoryName, 
                    ii.ItemClassId, ii.baseUnit, ff.frame, ff.id as frameId, 
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
                    ii.itemClassId, ii.baseUnit, ff.frame, ff.id as frameId, 
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
            group by v.inventoryCD, v.inventoryName, v.baseUnit,
                v.frame, v.frameId, v.qtyOnHand, iic.descr, v.aging
        ";

        return $base_sql;
    }

    public function get_warehouse_stock() {
        $base_sql = $this->_query_warehouse_stock();

        $searchable_columns = array('inventoryCD', 'inventoryName', 'frame', 'itemType');
        $column_order = array('inventoryCD', 'inventoryName', 'frame', 'qtyOnHand');
        $default_sort = "ORDER BY inventoryCD ASC";

        return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    }

    // private function _query_warehouse_stock() {
    //     $base_sql = "
    //         -- unit
    //         select distinct ii.InventoryCD, z.InventoryName, ff.frame, ff.id as frameId, z.qtyOnHand
    //         from InventoryItem as ii
    //         left join fmInventoryFrame as fif on ii.InventoryID = fif.inventoryId
    //         left join fmFrame as ff on fif.frameId = ff.id
    //         inner join (
    //             select InventoryID, InventoryCD, InventoryName, sum(QtyOnHand) as QtyOnHand
    //             from db_fmm.dbo.tb_InventoryBalance
    //             where CompanyID = 2
    //                 and QtyOnHand > 0
    //                 and FinPeriodID = (
    //                     select max(FinPeriodID)
    //                     from db_fmm.dbo.tb_InventoryBalance
    //                 )
    //             group by InventoryID, InventoryCD, InventoryName
    //         ) as z on fif.InventoryID = z.InventoryID
    //         where ii.CompanyID = 2
    //         union
    //         -- part
    //         select distinct ii.InventoryCD, z.InventoryName, ff.frame, ff.id as frameId, z.qtyOnHand
    //         from InventoryItem as ii
    //         left join fmPartFrame as fpf on ii.InventoryID = fpf.partInventoryId
    //         left join fmFrame as ff on fpf.frameId = ff.id
    //         inner join (
    //             select InventoryID, InventoryCD, InventoryName, sum(QtyOnHand) as QtyOnHand
    //             from db_fmm.dbo.tb_InventoryBalance
    //             where CompanyID = 2
    //                 and QtyOnHand > 0
    //                 and FinPeriodID = (
    //                     select max(FinPeriodID)
    //                     from db_fmm.dbo.tb_InventoryBalance
    //                 )
    //             group by InventoryID, InventoryCD, InventoryName
    //         ) as z on fpf.partInventoryID = z.InventoryID
    //         where ii.CompanyID = 2
    //     ";

    //     return $base_sql;
    // }

    // public function get_warehouse_stock() {
    //     $base_sql = "
    //         select InventoryCD, InventoryName, frame, frameId, qtyOnHand
    //         from (" . $this->_query_warehouse_stock() . ") as z
    //     ";

    //     $searchable_columns = array('InventoryCD', 'InventoryName', 'frame');
    //     $column_order = array('InventoryCD', 'InventoryName', 'frame', 'qtyOnHand');
    //     $default_sort = "ORDER BY InventoryCD ASC";

    //     return $this->datatable_handler->handle($base_sql, $searchable_columns, $column_order, $default_sort);
    // }
}
