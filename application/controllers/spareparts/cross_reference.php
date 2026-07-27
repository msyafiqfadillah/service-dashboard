<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Cross_reference extends CI_Controller {
        function __construct() {
            parent::__construct();

            $this->load->model("Inventory_model");
        }

        public function index() {
            $this->load->view('layout/site_tpl', array(
                "title" => "Cross Reference - FMM Service Dashboard",
                "page_title" => "Cross Reference",
                "page_subtitle" => "Cross Reference",
                "active_menu" => "spareparts/cross_reference",
                "content" => "spareparts/cross_reference",
                "data" => array(
                    "get_customers_by_part_url" => site_url('spareparts/cross_reference/get_customers_by_part')
                )
            ));
        }

        public function get_customers_by_part() {
            $part = $this->input->get("part");

            $result = $this->Inventory_model->get_customers_by_part($part);

            echo json_encode($result);
        }
    }
?>