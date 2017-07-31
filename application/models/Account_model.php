<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Account_model extends MY_Model {
    
    public function __construct() {
        parent::__construct();
        
        $this->table = DB_ACCOUNT;
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
    
    /*
     * update the user account access. If a record does not exist, a new one is created.
     */
    public function update_user_account_access($account_id, $user_id, $is_default_account = false, $uac = UAC_3) {
        
        $user_account_access = $this->uac->get([], ['account_id' => $account_id, 'user_id' => $user_id]);
        if($user_account_access) {
            $this->uac->update(['uac' => $uac], ['account_id' => $account_id, 'user_id' => $user_id]);
        } else {
            $this->uac->insert(['account_id' => $account_id, 'user_id' => $user_id, 'uac' => $uac]);
        }
        
        if($is_default_account) {
            //make all other accounts non-default
            $this->uac->update(['default_account' => !$is_default_account], ['user_id' => $user_id]);
            //set this account to be the default
            $this->uac->update(['default_account' => $is_default_account], ['account_id' => $account_id, 'user_id' => $user_id]);
        }
    }
}