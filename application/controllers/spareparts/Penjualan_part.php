<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Penjualan_part extends CI_Controller {
        function __construct() {
            parent::__construct();

            $this->load->model("Inventory_model");
        }

        public function index() {
            $current_year = date('Y');
            $one_year_ago = $current_year - 1;
            $two_years_ago = $current_year - 2;

            $this->load->view('layout/site_tpl', array(
                "title" => "Penjualan Sparepart - FMM Service Dashboard",
                "page_title" => "Penjualan Sparepart",
                "page_subtitle" => "Penjualan Sparepart {$two_years_ago} – {$current_year}",
                "active_menu" => "spareparts/penjualan_part",
                "content" => "spareparts/penjualan_part",
                "data" => array(
                    "sparepart_sales" => site_url('spareparts/penjualan_part/get_sparepart_sales'),
                    "current_year" => $current_year,
                    "one_year_ago" => $one_year_ago,
                    "two_years_ago" => $two_years_ago
                )
            ));
        }

        public function get_sparepart_sales() {
            $result = $this->Inventory_model->get_sparepart_sales();

            echo json_encode($result);
        }

        public function get_top_customers() {
            $inventoryCd = trim($this->input->get("inventoryCd"));

            $result = $this->Inventory_model->get_top_customers($inventoryCd);

            echo json_encode($result);
        }
    }
?>