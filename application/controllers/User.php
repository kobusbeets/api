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
            $user_query = $this->m_user->get(['id'], ['username' => $username], 1, 0, true);
            if($user_query) {
                $user_id = $user_query->id;
                
                $this->response->message = 'user is now linked to account';
            } else {
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
                $this->m_account->update_user_account_control($user_id, $account_id, 'fullaccess', true);
                
                $this->response->message = 'new user account created';
            }
            
            //link the user to this account without changing that user's default account
            $this->m_account->update_user_account_control($user_id, $this->userdata['account_id'], 'selected_permissions');
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
        //get input fields
        $username = $this->get_input('username');
        $password = $this->get_input('password');
        $email = $this->get_input('email');
        $mobile = $this->get_input('mobile');
        $firstname = $this->get_input('firstname');
        $lastname = $this->get_input('lastname');
        $permissions = $this->get_input('permissions');
        
        //run validation rules
        if($username !== null && empty($username)) {
            $this->response->message = 'username input is required';
        } elseif($password !== null && empty($password)) {
            $this->response->message = 'password input is required';
        } //additional rules: valid email address, valid mobile number
        elseif($email !== null && !valid_email($email)) {
            $this->response->message = 'the provided email address is not valid';
        }
        elseif($username !== null && $this->m_user->get(['id'], ['username' => $username], 1)) {
            $this->response->message = 'username is already taken, try another';
        }
        else {
            //check if username is unique, could be a validation rule
            
            //prepare the user data
            $db_user_data = []; 
            if($username !== null) 
                $db_user_data['username'] = $username;
            if($password !== null) 
                $db_user_data['password'] = encrypt_password($password);
            
            //update the user account if there are anything to update
            if(!empty($db_user_data)) {
                $db_user_where = [
                    'id' => $id
                ];
                $this->m_user->update($db_user_data, $db_user_where);
            }
            
            //prepare the user meta data
            $db_user_meta_data = []; 
            if($email !== null) 
                $db_user_meta_data['email'] = $email;
            if($mobile !== null) 
                $db_user_meta_data['mobile'] = $mobile;
            if($firstname !== null) 
                $db_user_meta_data['firstname'] = $firstname;
            if($lastname !== null) 
                $db_user_meta_data['lastname'] = $lastname;
            
            //update the user meta if there are anything to update
            if(!empty($db_user_meta_data)) {
                $db_user_meta_where = [
                    'user_id' => $id
                ];
                $this->m_user_meta->update($db_user_meta_data, $db_user_meta_where);
            }
            
            //did the request supply user permissions?
            if($permissions !== null) {
                //validate permissions against system permissions
                $validate_permissions = explode(',', $permissions);
                foreach($validate_permissions as $i=>$permission) {
                    if(!in_array($permission, SYSTEM_PERMISSIONS)) {
                        unset($validate_permissions[$i]);
                    }
                }
                
                //update user permissions
                $this->m_account->update_user_account_control($id, $this->userdata['account_id'], implode(',', $validate_permissions));
            }
            
            $this->response->message = 'user updated';
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
