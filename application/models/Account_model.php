<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Account_model extends MY_Model {
    
    public function __construct() {
        parent::__construct();
        
        $this->table = DB_ACCOUNT;
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
    
    /*
     * update the user account access. If a record does not exist, a new one is created.
     * params:
     * * user_id - the user's unique id in the database
     * * account_id - the account the user should have access to
     * * permissions - the commaseparated list of user permissions
     * * is_default_account - whether this should be the default account or not
     */
    public function update_user_account_control($user_id, $account_id, $permissions = '', $is_default_account = false) {
        $user_account_control = $this->m_uac->get([], ['user_id' => $user_id, 'account_id' => $account_id]);
        if($user_account_control) {
            $this->m_uac->update(['permissions' => $permissions], ['user_id' => $user_id, 'account_id' => $account_id]);
        } else {
            $this->m_uac->insert(['user_id' => $user_id, 'account_id' => $account_id, 'permissions' => $permissions]);
        }
        
        if($is_default_account) {
            //make all other accounts non-default
            $this->m_uac->update(['default_account' => !$is_default_account], ['user_id' => $user_id]);
            //set this account to be the default
            $this->m_uac->update(['default_account' => $is_default_account], ['user_id' => $user_id, 'account_id' => $account_id]);
        }
    }
}