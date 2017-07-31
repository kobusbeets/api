<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends MY_Controller {
    
    public function index() {}

    public function get($id = null) {
        echo json_encode([
            'status' => false,
            'response' => [],
            'message' => 'get ' . $id
        ]);
    }
    
    public function create() {
        echo json_encode([
            'status' => false,
            'response' => [],
            'message' => 'create'
        ]);
    }
    
    public function update($id = null) {
        echo json_encode([
            'status' => false,
            'response' => [],
            'message' => 'update ' . $id
        ]);
    }
    
    public function delete($id = null) {
        echo json_encode([
            'status' => false,
            'response' => [],
            'message' => 'delete ' . $id
        ]);
    }
}
