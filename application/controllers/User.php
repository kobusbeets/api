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
        //validate the request input
        if(!$this->request_input->Username) {
            $this->response->message = 'Username is required';
        } elseif(!$this->request_input->Password) {
            $this->response->message = 'Password is required';
        } elseif(!$this->request_input->EmailAddress) {
            $this->response->message = 'Email address is required';
        } elseif(!valid_email($this->request_input->EmailAddress)) {
            $this->response->message = 'Invalid email address';
        } else {
            //check if the username is available
            $user = $this->m_user->get(['id'], ['username' => $this->request_input->Username], 1);
            if($user) {
                $this->response->message = 'Username is already taken';
            } else {
                //create a new account
                $account_id = $this->m_account->insert(['name' => $this->request_input->Username]);
                
                //create a new user
                $user_id = $this->m_user->insert([
                    'username' => $this->request_input->Username,
                    'password' => $this->request_input->Password
                ]);
                
                //save the user meta info
                $this->m_user_meta->insert([
                    'user_id' => $user_id,
                    'email' => $this->request_input->EmailAddress,
                    'email_code' => md5($user_id),
                    'firstname' => $this->request_input->Firstname,
                    'lastname' => $this->request_input->Lastname
                ]);
                
                //link the user to the new account
                $this->m_uac->insert([
                    'user_id' => $user_id,
                    'account_id' => $account_id,
                    'default_account' => true,
                    'permissions' => 'fullaccess'
                ]);
                
                //send the activation email 
                //send_email($this->request_input->EmailAddress, "activation email", 'the message');
                
                //change the response message
                $this->response->status = true;
                $this->response->message = 'new user account created';
            }
        }
    }
    
    public function signin() {
        /*
        $this->response->data['token'] = bin2hex(random_bytes(16));
        //insert the new api token
        $this->m_api_token->insert([
            'user_id' => $this->response->data['user_id'],
            'token' => $this->response->data['token'],
            'date_expiry' => FUTURE_TOKEN_EXPIRY_DATE
        ]);
        //*/
    }
}
