<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Katalog_part_list extends CI_Controller {
        function __construct() {
            parent::__construct();

            $this->load->model("Inventory_model");
        }

        public function index() {
            $this->load->view('layout/site_tpl', array(
                "title" => "Katalog Sparepart - FMM Population Unit & Part",
                "page_title" => "Katalog Sparepart",
                "page_subtitle" => "Item dalam katalog",
                "active_menu" => "spareparts/katalog/katalog_part_list",
                "content" => "spareparts/katalog/katalog_part_list",
                "data" => array(
                    "katalog_part_list_url" => site_url('spareparts/katalog/katalog_part_list/get_part_list'),
                    "populasi_unit_url" => site_url('spareparts/katalog/katalog_part_list/get_populasi_unit'),
                    "get_part_details_url" => site_url('spareparts/katalog/katalog_part_list/get_part_details')
                )
            ));
        }

        public function get_part_list() {
            $result = $this->Inventory_model->get_part_list();

            echo json_encode($result);
        }

        public function get_populasi_unit() {
            $partCd = $this->input->post('partCd');
            $result = $this->Inventory_model->get_populasi_unit($partCd);

            echo json_encode($result);
        }

        public function get_part_details() {
            $partCd = $this->input->post('partCd');
            $result = $this->Inventory_model->get_part_frames_assemblies($partCd);

            echo json_encode($result);
        }
    }
?>