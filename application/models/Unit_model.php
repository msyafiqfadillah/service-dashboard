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
