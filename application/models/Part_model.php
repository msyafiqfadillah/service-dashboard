<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Part_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _query_part_list() {
        $base_sql = "
            select partId, partCode, part, unitId, unitCode, frame, application, qtyOnHand
            from (
                select fpf.partInventoryId as partId, fpf.partInventoryCd as partCode, 
                    cast(fpf.descr as varchar(max)) AS part,
                    fif.inventoryId as unitId, fif.inventoryCd as unitCode, 
                    ff.frame, cast(fpf.application as varchar(max)) as application, 
                    sum(x.qtyOnHand) as qtyOnHand
                from fmFrame as ff 
                inner join fmInventoryFrame as fif on ff.id = fif.frameId
                inner join fmPartFrame as fpf on ff.id = fpf.frameId
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
                group by fpf.partInventoryId, fpf.partInventoryCd, cast(fpf.descr as varchar(max)),
                    fif.inventoryId, fif.inventoryCd, ff.frame, cast(fpf.application as varchar(max))
            ) as z
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
            $search_like = $this->db->escape('%' . $search_value . '%');
            $where_sql .= " AND (
                partCode LIKE {$search_like} OR 
                part LIKE {$search_like} OR 
                unitCode LIKE {$search_like} OR 
                frame LIKE {$search_like}
            )";
        }

        $recordsTotal = $this->db->query($count_base_sql)->row()->total;

        $recordsFiltered = $this->db->query($count_base_sql . $where_sql)->row()->total;

        $column_order = array('partCode', 'part', 'inventoryCd', 'frame', 'application');
        $order_sql = " ORDER BY partCode ASC"; // default
        
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
}
