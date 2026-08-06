<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Katalog_airend_rotary extends CI_Controller {
        function __construct() {
            parent::__construct();

            $this->load->model("Inventory_model");
        }

        public function index() {
            $filter_options = $this->Inventory_model->get_airend_rotary_filter_options();

            $this->load->view('layout/site_tpl', array(
                "title" => "Katalog Airend Rotary - FMM Population Unit & Part",
                "page_title" => "Katalog Airend Rotary",
                "page_subtitle" => "Airend Rotary",
                "active_menu" => "spareparts/katalog/katalog_airend_rotary",
                "content" => "spareparts/katalog/katalog_airend_rotary",
                "data" => array(
                    "get_airend_rotary_url" => site_url('spareparts/katalog/katalog_airend_rotary/get_data'),
                    "regions" => $filter_options['regions'],
                    "categories" => $filter_options['categories']
                )
            ));
        }

        public function get_data() {
            $result = $this->Inventory_model->get_airend_rotary();
            echo json_encode($result);
        }
    }
?>
