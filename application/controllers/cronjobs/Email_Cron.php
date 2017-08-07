<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_Cron extends CI_Controller {

    public function index() {
        //quit if not running via cli
        if(!is_cli()) {
            echo 'The email cron can only be run via command line.';
            exit;
        }
        
        echo 'running email cron' . PHP_EOL;
    }
}
