<?php
    defined('BASEPATH') or exit('No direct script access allowed');

    class Auth extends CI_Controller
    {
        function __construct()
        {
            parent::__construct();

            $this->db = $this->load->database('default', true);
        }

        public function index($bra, $id_grup, $email, $initial)
        {
            $this->login($bra, $id_grup, $email, $initial);
        }

        public function login($bra, $id_grup, $email = null, $employee_initial = null)
        {
            if ($bra == null || $bra == "") {

            } else {
                $sess = array(
                    'branch_id' => $bra,
                    'id_grup' => $id_grup,
                    'employee_initial' => $employee_initial,
                    'email' => base64_decode($email),
                    'logged_in' => true
                );

                $this->session->set_userdata($sess);

                redirect(base_url('dashboard'));
            }
        }

        public function logout()
        {
            $this->session->sess_destroy();

            redirect('https://eps.fajarmasmurni.com', 'refresh');
        }

        public function log()
        {
            $this->session->set_userdata('branch_id', 1);
        }
    }
?>
