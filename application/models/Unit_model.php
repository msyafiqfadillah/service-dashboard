<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    private function _query_populasi_unit($unitId) {
        // $base_sql = "
        //     select MasterUnitID, a.CustomerID, 
        //         b.CustomerName, BranchID, a.InventoryClassID, 
        //         c.InventoryClassCode, c.InventoryClassName, 
        //         InventoryID, InventoryName, SerialNumber
        //     from FMMService.dbo.MasterUnit a
        //     left join FMMService.dbo.Customer b on a.CustomerID = b.CustomerID
        //     left join FMMService.dbo.InventoryClass c on a.InventoryClassID = c.InventoryClassID
        //     where RowStatus = 1 and IsActive = 1 and InventoryID = '$unitId'
        // ";

        $base_sql = "
            select distinct a.CustomerID, 
                b.CustomerName, BranchID, a.InventoryClassID, 
                c.InventoryClassCode, c.InventoryClassName, 
                InventoryID, InventoryName
            from FMMService.dbo.MasterUnit a
            left join FMMService.dbo.Customer b on a.CustomerID = b.CustomerID
            left join FMMService.dbo.InventoryClass c on a.InventoryClassID = c.InventoryClassID
            where RowStatus = 1 and IsActive = 1 and InventoryID = '$unitId'
        ";

        return $base_sql;
    }

    public function get_populasi_unit($unitId) {
        $query = $this->_query_populasi_unit($unitId);
        $result = $this->db->query($query)->result();

        return $result;
    }
}
