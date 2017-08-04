<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bad_Request extends MY_Controller {

    public function index() {
        
    }
    
    public function request_not_valid() {
        $this->response->status = false;
        $this->response->response = [];
        $this->response->message = 'request_not_valid';
    }
    
    public function not_found() {
        $this->response->status = false;
        $this->response->response = [];
        $this->response->message = 'not_found';
    }
}
