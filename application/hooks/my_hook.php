<?php
    class My_hook {
        public function check_auth() {
            if (php_sapi_name() === 'cli' || defined('STDIN')) {
                return;
            }

            $check_routes = check_routes_segement_public("routes");

            // check session
            if (!$check_routes) {
                $is_logged = get_session("logged_in");

                if (!$is_logged) {
                    show_error("You have been logout, please go to <a href=\"https://fmm-eps.com/dasbor\">this url</a>", 401, "Forbidden Action");   
                }
            }   
        }
    }