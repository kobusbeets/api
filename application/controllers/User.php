<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    /*
     * Allowed inputs:
     * - search (based on name and content)
     * - status
     * - priority
     * - assigned_user_id
     * - read
     */
    public function get($id = null) {
        //set allowed query params, everything else is ignored
        $allowed_filter_keys = [
            'search', //special treatment
            'assigned_user_id', 'status', 'priority', 'read'
        ];
        
        //check if a single item is queried
        if($id) {
            $this->db_where['id'] = $id;
        }
        
        //do not perform a search when a single item is being queried
        if(!$id && $this->input->get()) {
            foreach($allowed_filter_keys as $allowed_filter_key) {
                //skip empty filters
                if(($filter_value = $this->input->get($allowed_filter_key))) {
                    //special treatment for the search filter
                    if($allowed_filter_key == 'search') {
                        $this->db_where[$allowed_filter_key] = 'MATCH (name, content) AGAINST ("' . $filter_value . '")';
                    } else {
                        //check if a not where query should run
                        if(substr($filter_value, 0, 1) == '!') {
                            $this->db_where[$allowed_filter_key . ' !='] = substr($filter_value, 1, strlen($filter_value));
                        } else {
                            $this->db_where[$allowed_filter_key] = $filter_value;
                        }
                    }
                }
            }
        }
        
        $this->response->status = true;
        $this->response->response = $this->m_ticket->get([], $this->db_where, $id ? 1 : ($this->input->get('limit') ?? false), ($this->input->get('offset') ?? false));
    }
    
    /*
     * Allowed inputs:
     * - name
     * - content
     * - status
     * - priority
     * - assigned_user_id
     * - read (maybe)
     */
    public function create() {
        //get input values
        $username = $this->get_input('username');
        $password = $this->get_input('password');
        $email = $this->get_input('email');
        $mobile = $this->get_input('mobile');
        $firstname = $this->get_input('firstname');
        $lastname = $this->get_input('lastname');
        
        //see if required fields are filled in
        if(empty($username)) {
            $this->response->message = 'username input is required';
        } elseif(empty($password)) {
            $this->response->message = 'password input is required';
        } else {
            
            //does the user exist? only create a uac record if so ;)
            
            $account_id = $this->m_account->insert(['name' => $username]);
            
            //create a new user
            $user_id = $this->m_user->insert([
                'username' => $username,
                'password' => $password,
                'account_id' => $account_id
            ]);

            //save the user meta info
            $this->m_user_meta->insert([
                'user_id' => $user_id,
                'email' => $email,
                'email_code' => md5($user_id),
                'mobile' => $mobile,
                'firstname' => $firstname,
                'lastname' => $lastname
            ]);

            //link the user to the new account
            $this->m_uac->insert([
                'user_id' => $user_id,
                'account_id' => $account_id,
                'default_account' => false,
                'permissions' => 'fullaccess'
            ]);
            
            //link the newly created user to this account also
            $this->m_uac->insert([
                'user_id' => $user_id,
                'account_id' => $this->userdata['account_id'],
                'default_account' => true,
                'permissions' => 'selected_permissions'
            ]);
        }
    }
    
    /*
     * Allowed inputs:
     * - status
     * - priority
     * - assigned_user_id
     * - read
     */
    public function update($id = null) {
        //define allowed inputs and if it's required or not
        $allowed_inputs = [
            'status' => false, 'priority' => false, 'assigned_user_id' => false, 'read' => false
        ];
        
        $validation_failed = false;
        
        foreach($allowed_inputs as $allowed_input=>$required) {
            //get the input value
            $input_value = $this->get_input($allowed_input);
            
            //check input validation
            if($required && empty($input_value)) {
                $this->response->message = $allowed_input . ' input is required';
                $validation_failed = true;
                break;
            }
            
            //add input value to data array
            if($input_value !== null) {
                //make sure the status is in the allowed list of statuses
                if($allowed_input == 'status' && !in_array($input_value, TS_LIST))
                    $input_value = TS_DEFAULT;
                
                //make sure priority is in the list of allowed priorities
                if($allowed_input == 'priority' && !in_array($input_value, TP_LIST))
                    $input_value = TP_DEFAULT;
                
                $this->db_data[$allowed_input] = $input_value;
            }
        }
        
        //if no status is passed, set the default status
        if(!isset($this->db_data['status']))
            $this->db_data['status'] = TS_DEFAULT;
        
        //if no priority is passed, set the default priority
        if(!isset($this->db_data['priority']))
            $this->db_data['priority'] = TP_DEFAULT;
        
        //if validation is successful, create the new ticket
        if(!$validation_failed) {
            //set the record id
            $this->db_where['id'] = $id;
            
            //create a new ticket resource
            $this->m_ticket->update($this->db_data, $this->db_where);
            
            $this->response->response = $this->m_ticket->get([], $this->db_where, 1);
            $this->response->message = 'ticket updated';
        }
    }
    
    /*
     * Allowed inputs:
     * - id
     */
    public function delete($id = null) {
        $this->db_where['id'] = $id;
        $affected_records = $this->m_ticket->delete($this->db_where);
        if($affected_records) {
            $this->response->message = $affected_records . ' ticket/s deleted';
        } else {
            $this->response->message = 'nothing was deleted';
        }
    }
    
    public function signup() {
        //get input data
        $username = $this->get_input('Username');
        $password = $this->get_input('Password');
        $email_address = $this->get_input('EmailAddress');
        $firstname = $this->get_input('Firstname');
        $lastname = $this->get_input('Lastname');
        //validate the request input
        if(!$username) {
            $this->response->message = 'Username is required';
        } elseif(!$password) {
            $this->response->message = 'Password is required';
        } elseif(!$email_address) {
            $this->response->message = 'Email address is required';
        } elseif(!valid_email($email_address)) {
            $this->response->message = 'Invalid email address';
        } else {
            //check if the username is available
            $user = $this->m_user->get(['id'], ['username' => $username], 1);
            if($user) {
                $this->response->message = 'Username is already taken';
            } else {
                //create a new account
                $account_id = $this->m_account->insert(['name' => $username]);
                
                //create a new user
                $user_id = $this->m_user->insert([
                    'username' => $username,
                    'password' => $password,
                    'account_id' => $account_id
                ]);
                
                //save the user meta info
                $this->m_user_meta->insert([
                    'user_id' => $user_id,
                    'email' => $email_address,
                    'email_code' => md5($user_id),
                    'firstname' => $firstname,
                    'lastname' => $lastname
                ]);
                
                //link the user to the new account
                $this->m_uac->insert([
                    'user_id' => $user_id,
                    'account_id' => $account_id,
                    'default_account' => true,
                    'permissions' => 'fullaccess'
                ]);
                
                //send the activation email 
                //send_email($email_address, "activation email", 'the message');
                
                //change the response message
                $this->response->status = true;
                $this->response->message = 'new user account created';
            }
        }
    }
    
    public function signin() {
        //check if user is authenticated
        if($this->userdata['is_authenticated']) {
            //check if token id is 
            if(!isset($this->response->response['token']) || empty($this->response->response['token'])) {
                //delete all other tokens
                $this->m_api_token->delete(['user_id' => $this->userdata['id']]);
                //generate a new API token
                $this->response->response['token'] = bin2hex(random_bytes(16));
                $this->response->response['token_expiry'] = date('F j, Y H:i:s', FUTURE_TOKEN_EXPIRY_DATE);
                //insert the new api token
                $this->m_api_token->insert([
                    'user_id' => $this->userdata['id'],
                    'token' => $this->response->response['token'],
                    'date_expiry' => FUTURE_TOKEN_EXPIRY_DATE
                ]);
                //send a response message
                $this->response->message = 'new token issued';
            }
            
            $this->response->status = true;
        }
    }
}
