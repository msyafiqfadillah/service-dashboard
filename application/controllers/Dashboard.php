<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function index()
    {
        $this->_load_maintenance('Dashboard Aftermarket', 'dashboard');
    }

    public function populasi()
    {
        $this->_load_maintenance('Populasi Unit', 'populasi');
    }

    public function master()
    {
        $this->_load_maintenance('Master Unit', 'master');
    }

    public function riwayat()
    {
        $this->_load_maintenance('Riwayat Servis', 'riwayat');
    }

    public function penjualan()
    {
        $this->_load_maintenance('Penjualan Sparepart', 'penjualan');
    }

    public function crossref()
    {
        $this->_load_maintenance('Cross-Reference', 'crossref');
    }

    public function jadwalpm()
    {
        $this->_load_maintenance('Jadwal PM (CCN)', 'jadwalpm');
    }

    private function _load_maintenance($title, $active_menu)
    {
        $this->load->view('layout/site_tpl', array(
            "title" => $title . " - FMM Service Dashboard",
            "page_title" => $title,
            "page_subtitle" => "Halaman ini sedang dalam tahap pengembangan",
            "active_menu" => $active_menu,
            "content" => "under_maintenance"
        ));
    }
}
