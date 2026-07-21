<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
	public function index()
	{
		$this->load->view('layout/site_tpl', array(
            "title" => "FMM Service Dashboard Dashboard",
            "page_title" => "Dashboard Aftermarket",
            "page_subtitle" => "Ringkasan populasi unit, jadwal PM, dan distribusi branch",
            "active_menu" => "dashboard",
            "content" => "dashboard"
        ));
	}
}
