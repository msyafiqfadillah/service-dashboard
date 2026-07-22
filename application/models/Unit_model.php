<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    private function _query_populasi_unit($unitId) {
        $base_sql = "
            select distinct a.CustomerID, 
                b.CustomerName, br.BranchCD, a.InventoryClassID, 
                c.InventoryClassCode, c.InventoryClassName, 
                InventoryID, InventoryName, SerialNumber
            from FMMService.dbo.MasterUnit as a
            left join FMMService.dbo.Customer as b on a.CustomerID = b.CustomerID
            left join FMMService.dbo.InventoryClass as c on a.InventoryClassID = c.InventoryClassID
            inner join AcumaticaProduction_NEW.dbo.Branch as br on a.BranchID = br.BranchID 
            where RowStatus = 1 and IsActive = 1 and br.CompanyId = 2 and InventoryID = '$unitId'
        ";

        return $base_sql;
    }

    public function get_populasi_unit($unitId) {
        $query = $this->_query_populasi_unit($unitId);
        $result = $this->db->query($query)->result();

        return $result;
    }
}
