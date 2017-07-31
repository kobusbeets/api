<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bad_Request extends MY_Controller {

    public function index() {
        
    }
    
    public function request_not_valid() {
        echo json_encode([
            'status' => false,
            'response' => [],
            'message' => 'request_not_valid'
        ]);
    }
    
    public function not_found() {
        echo json_encode([
            'status' => false,
            'response' => [],
            'message' => 'not_found'
        ]);
    }
}
