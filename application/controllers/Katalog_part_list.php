<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Katalog_part_list extends CI_Controller {
        function __construct() {
            parent::__construct();

            $this->load->model("Part_model");
            $this->load->model("Unit_model");
        }

        public function index() {
            $this->load->view('layout/site_tpl', array(
                "title" => "Katalog Sparepart - FMM Service Dashboard",
                "page_title" => "Katalog Sparepart",
                "page_subtitle" => "Item dalam katalog",
                "active_menu" => "katalog_part_list",
                "content" => "katalog_part_list",
                "data" => array(
                    "katalog_part_list_url" => site_url('katalog_part_list/get_part_list'),
                    "populasi_unit_url" => site_url('katalog_part_list/get_populasi_unit')
                )
            ));
        }

        public function get_part_list() {
            $result = $this->Part_model->get_part_list();

            echo json_encode($result);
        }

        public function get_populasi_unit() {
            $unitId = $this->input->post('unitId');

            $result = $this->Unit_model->get_populasi_unit($unitId);

            echo json_encode($result);
        }
    }
?>