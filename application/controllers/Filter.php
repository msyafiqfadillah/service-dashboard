<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Filter extends CI_Controller {
        function __construct() {
            parent::__construct();

            $this->load->model("Branch_model");
        }

        public function get_branch() {
            $result = $this->Branch_model->get_branch();

            echo json_encode($result);
        }
    }
?>