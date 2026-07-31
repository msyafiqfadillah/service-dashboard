<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model("Inventory_model");
    }

    public function index()
    {
        $this->load->view('layout/site_tpl', array(
            "title" => "Dashboard Aftermarket - FMM Population Unit & Part",
            "page_title" => "Dashboard Aftermarket",
            "page_subtitle" => "Distribusi populasi unit per wilayah/kantor cabang",
            "active_menu" => "dashboard",
            "content" => "dashboard",
            "data" => array(
                "get_unit_distribution_url" => site_url('dashboard/get_unit_distribution'),
                "get_branch_details_url" => site_url('dashboard/get_branch_details')
            )
        ));
    }

    public function get_unit_distribution() {
        $result = $this->Inventory_model->get_unit_distribution();

        echo json_encode($result);
    }

    public function get_branch_details() {
        $branch = $this->input->get("branch");
        $result = $this->Inventory_model->get_populasi_unit_by_branch($branch);

        echo json_encode($result);
    }

    public function jadwalpm()
    {
        $this->_load_maintenance('Jadwal PM (CCN)', 'jadwalpm');
    }

    private function _load_maintenance($title, $active_menu)
    {
        $this->load->view('layout/site_tpl', array(
            "title" => $title . " - FMM Population Unit & Part",
            "page_title" => $title,
            "page_subtitle" => "Halaman ini sedang dalam tahap pengembangan",
            "active_menu" => $active_menu,
            "content" => "under_maintenance"
        ));
    }
}
