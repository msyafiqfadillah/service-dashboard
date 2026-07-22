<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Stok_gudang extends CI_Controller {
        function __construct() {
            parent::__construct();

            $this->load->model("Inventory_model");
        }

        public function index() {
            $this->load->view('layout/site_tpl', array(
                "title" => "Stok Gudang - FMM Service Dashboard",
                "page_title" => "Stok Gudang",
                "page_subtitle" => "Stok Gudang",
                "active_menu" => "spareparts/stok_gudang",
                "content" => "spareparts/stok_gudang",
                "data" => array(
                    "stok_gudang_url" => site_url('spareparts/stok_gudang/get_stok_gudang')
                )
            ));
        }

        public function get_stok_gudang() {
            $result = $this->Inventory_model->get_warehouse_stock();

            echo json_encode($result);
        }
    }
?>