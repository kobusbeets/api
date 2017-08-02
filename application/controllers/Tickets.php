<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends MY_Controller {
    
    public function index() {}

    public function get($id = null) {
        $where = ['account_id' => $this->userdata['account_id']];
        
        if($id) {
            $where['id'] = $id;
        }
        
        $this->response->status = true;
        $this->response->response = $this->m_ticket->get([], $where, $id ? 1 : false);
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
