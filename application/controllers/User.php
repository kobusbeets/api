<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
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
                    'password' => $password
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
