<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

    public function index() {
        echo 'Welcome to the API! <br><br>';
        
        echo date('Y-m-d H:i:s', strtotime('next month')); //for initial account subscription expiry
    }
}
