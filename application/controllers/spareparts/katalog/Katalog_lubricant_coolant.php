<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Katalog_lubricant_coolant extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model("Inventory_model");
    }

    public function index() {
        $this->load->view('layout/site_tpl', array(
            "title" => "Katalog Lubricant & Coolant - FMM Population Unit & Part",
            "page_title" => "Katalog Lubricant & Coolant",
            "page_subtitle" => "37 produk (coolant, grease, lubricant reciprocating/rotary) · sumber: Master_PartList_Lubricant_&_Coolant.csv",
            "active_menu" => "spareparts/katalog/katalog_lubricant_coolant",
            "content" => "spareparts/katalog/katalog_lubricant_coolant",
            "data" => array(
                "get_data_url" => site_url('spareparts/katalog/katalog_lubricant_coolant/get_data'),
                "get_lubricant_details_url" => site_url('spareparts/katalog/katalog_lubricant_coolant/get_lubricant_details'),
                "populasi_unit_url" => site_url('spareparts/katalog/katalog_lubricant_coolant/get_populasi_unit')
            )
        ));
    }

    public function get_data() {
        $result = $this->Inventory_model->get_lubricant_coolant();
        echo json_encode($result);
    }

    public function get_lubricant_details() {
        $ccn = trim($this->input->post("ccn"));
        if (!$ccn) {
            $ccn = trim($this->input->post("partCd"));
        }
        $result = $this->Inventory_model->get_lubricant_details($ccn);
        echo json_encode($result);
    }

    public function get_populasi_unit() {
        $ccn = trim($this->input->post("ccn"));
        if (!$ccn) {
            $ccn = trim($this->input->post("partCd"));
        }
        $result = $this->Inventory_model->get_populasi_unit_by_lubricant($ccn);
        echo json_encode($result);
    }
}
?>
