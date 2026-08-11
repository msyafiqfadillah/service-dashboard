<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Katalog_centac extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model("Inventory_model");
    }

    public function index() {
        $this->load->view('layout/site_tpl', array(
            "title" => "Centac Catalog - FMM Population Unit & Part",
            "page_title" => "Centac Catalog",
            "page_subtitle" => "sumber: BOM per Customer/Frame/Serial",
            "active_menu" => "spareparts/katalog/katalog_centac",
            "content" => "spareparts/katalog/katalog_centac",
            "data" => array(
                "get_data_url" => site_url('spareparts/katalog/katalog_centac/get_data')
            )
        ));
    }

    public function get_data() {
        $result = $this->Inventory_model->get_centac();
        echo json_encode($result);
    }
}
?>
