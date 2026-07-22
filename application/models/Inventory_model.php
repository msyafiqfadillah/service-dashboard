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
            select partCd, partDesc, assemblySection, frame, application, qtyOnHand
            from (
                select partCd, partDesc, assemblySection, frame, application, sum(qtyOnhand) as qtyOnHand
                    --, unitCd, unitDescr
                from (
                    select fpf.partInventoryCd as partCd, cast(fpf.descr as varchar(max)) as partDesc, 
                        cast(fpf.assemblySection as varchar(max)) as assemblySection, 
                        cast(ff.frame as varchar(max)) as frame, cast(fpf.application as varchar(max)) as application,
                        x.qtyOnHand
                        --, fif.inventoryCd as unitCd, cast(fif.descr as varchar(max)) as unitDescr
                    from fmPartFrame as fpf
                    inner join InventoryItem as ii on fpf.partInventoryId = ii.InventoryID
                    left join fmFrame as ff on fpf.frameId = ff.id
                    -- di left join karena ada unit yang belum masuk
                    left join fmInventoryFrame as fif on ff.id = fif.frameId
                    inner join (
                        select id, CompanyID, BranchID, BranchCD, InventoryID, InventoryCD, InventoryName, QtyOnHand, ItemClass
                        from db_fmm.dbo.tb_InventoryBalance
                        where CompanyID = 2
                            and QtyOnHand > 0
                            and FinPeriodID = (
                                select max(FinPeriodID)
                                from db_fmm.dbo.tb_InventoryBalance
                        ) 
                    ) as x on fpf.partInventoryId = x.InventoryID
                    group by fpf.partInventoryCd, cast(fpf.descr as varchar(max)), cast(fpf.assemblySection as varchar(max)),
                        cast(ff.frame as varchar(max)), cast(fpf.application as varchar(max)), x.qtyOnHand
                        -- , fif.inventoryCd, cast(fif.descr as varchar(max))
                ) as z
                group by partCd, partDesc, assemblySection, frame, application
            ) as y
        ";

        return $base_sql;
    }

    public function get_part_list() {
        $requestData = $this->input->post();

        $base_sql = $this->_query_part_list();

        $count_base_sql = "
            select count(*) as total 
            from ($base_sql) as ttl
        ";

        $search_value = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
        $where_sql = "where 1=1";
        
        if (!empty($search_value)) {
            $search_like = "'%" . $search_value . "%'";
            $where_sql .= " AND (
                partCd LIKE {$search_like} OR 
                partDesc LIKE {$search_like} OR 
                [assemblySection] LIKE {$search_like} OR 
                [application] LIKE {$search_like} OR 
                frame LIKE {$search_like}
            )";
        }

        $recordsTotal = $this->db->query($count_base_sql)->row()->total;

        $recordsFiltered = $this->db->query($count_base_sql . $where_sql)->row()->total;

        $column_order = array('partCd', 'partDesc', 'frame', 'assemblySection', 'application');
        $order_sql = " ORDER BY partCd ASC"; // default
        
        if (isset($requestData['order'])) {
            $col_idx = (int)$requestData['order']['0']['column'];
            $order_dir = strtoupper($requestData['order']['0']['dir']) === 'DESC' ? 'DESC' : 'ASC';
            if (isset($column_order[$col_idx]) && $column_order[$col_idx] !== null) {
                $order_sql = " ORDER BY " . $column_order[$col_idx] . " " . $order_dir;
            }
        }
        
        $start = isset($requestData['start']) ? (int)$requestData['start'] : 0;
        if (isset($requestData['length']) && $requestData['length'] != -1) {
            $length = (int)$requestData['length'];
        } else {
            $length = 10; // Default fallback to prevent memory crash
        }
        
        $final_sql = $base_sql . $where_sql . $order_sql . " OFFSET {$start} ROWS FETCH NEXT {$length} ROWS ONLY";
        $data = $this->db->query($final_sql)->result_array();

        $result = array(
            "draw" => isset($requestData['draw']) ? intval($requestData['draw']) : 1,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        );

        return $result;
    }

    private function _query_populasi_unit($unitId) {
        $base_sql = "
            select a.MasterUnitID, a.CustomerID, b.CustomerName, b.CustomerCode, 
                BranchCD, a.InventoryClassID, c.InventoryClassCode, c.InventoryClassName, 
                InventoryID, InventoryName, SerialNumber, d.HoursMeter
            from FMMService.dbo.MasterUnit a
            inner join AcumaticaProduction_NEW.dbo.Branch as br on a.BranchID = br.BranchID 
            left join FMMService.dbo.Customer b ON a.CustomerID = b.CustomerID
            left join FMMService.dbo.InventoryClass c ON a.InventoryClassID = c.InventoryClassID
            left join FMMService.dbo.MasterUnitHM d ON a.MasterUnitID = d.MasterUnitID
            where RowStatus = 1 AND IsActive = 1 and br.CompanyID = 2 and InventoryID = '$unitId'
        ";

        return $base_sql;
    }

    public function get_populasi_unit($unitId) {
        $query = $this->_query_populasi_unit($unitId);
        $result = $this->db->query($query)->result();

        return $result;
    }
}
