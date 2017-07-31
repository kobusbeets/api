<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Task_model extends MY_Model {
    
    public function __construct() {
        parent::__construct();
        
        $this->table = DB_TASK;
    }
    
    public function insert($data = []) {
        return parent::insert($data);
    }
    
    public function get($fields = array(), $where = array(), $limit = false, $offset = false) {
        return parent::get($fields, $where, $limit, $offset);
    }
    
    public function update($data = array(), $where = array(), $limit = false) {
        return parent::update($data, $where, $limit);
    }
    
    public function delete($where = array()) {
        return parent::delete($where);
    }
}