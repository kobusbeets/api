<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Account_subscription_model extends MY_Model {
    
    public function __construct() {
        parent::__construct();
        
        $this->table = DB_ACCOUNT_SUBSCRIPTION;
    }
    
    public function insert($data = []) {
        return parent::insert($data);
    }
    
    public function get($fields = array(), $where = array(), $limit = false, $offset = false, $return_single = true) {
        return parent::get($fields, $where, $limit, $offset, $return_single);
    }
    
    public function update($data = array(), $where = array(), $limit = false) {
        return parent::update($data, $where, $limit);
    }
    
    public function delete($where = array()) {
        return parent::delete($where);
    }
}