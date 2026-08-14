<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Katalog_centac extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model("Inventory_model");
    }

    public function index() {
        $filter_options = $this->Inventory_model->get_centac_filter_options();

        $this->load->view('layout/site_tpl', array(
            "title" => "Centac Catalog - FMM Population Unit & Part",
            "page_title" => "Centac Catalog",
            "page_subtitle" => "sumber: BOM per Customer/Frame/Serial",
            "active_menu" => "spareparts/katalog/katalog_centac",
            "content" => "spareparts/katalog/katalog_centac",
            "data" => array(
                "get_data_url" => site_url('spareparts/katalog/katalog_centac/get_data'),
                "customers" => $filter_options['customers'],
                "models" => $filter_options['models']
            )
        ));
    }

    public function get_data() {
        $customer = $this->input->post('customer');
        $model = $this->input->post('model');
        $stockStatus = $this->input->post('stockStatus');
        $result = $this->Inventory_model->get_centac($customer, $model, $stockStatus);
        echo json_encode($result);
    }
}
?>
