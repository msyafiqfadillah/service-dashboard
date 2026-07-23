<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Branch_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
    }

    private function _query_branch() {
        $base_sql = "
            select BranchCD
            from AcumaticaProduction_NEW.dbo.Branch as b
            where CompanyId = 2 and Active = 1
        ";

        return $base_sql;
    }

    public function get_branch() {
        $base_sql = $this->_query_branch();
        $result = $this->db->query($base_sql)->result();

        return $result;
    } 
}