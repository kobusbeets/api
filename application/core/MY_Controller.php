<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    
    public $request_input;
    
    public $userdata;
    
    public $response;

    public function __construct() {
        parent::__construct();
        
        //set the default timezone to Auckland
        date_default_timezone_set('Pacific/Auckland');
        
        $this->request_input = json_decode($this->input->raw_input_stream);
        
        $this->userdata = [
            'is_authenticated' => false
        ];
        
        $this->response = new stdClass();
        $this->response->status = false;
        $this->response->response = [];
        $this->response->message = '';
        
        //$this->output->enable_profiler(TRUE);
        
        if(!(in_array(strtolower($this->router->class), ['user', 'bad_request']) && in_array(strtolower($this->router->method), ['signup', 'request_not_valid']))) {
            $this->authenticate();
        }
    }
    
    public function get_input($key = null) {
        return $key && isset($this->request_input->$key) ? $this->request_input->$key : null;
    }

    public function authenticate() {
        $token = $this->input->get_request_header('AuthToken');
        if($token) {
            $this->authenticate_by_token($token);
        } else {
            $this->authenticate_by_user_credentials();
        }
    }
    
    private function authenticate_by_token($token) {
        $token_data_query = $this->m_api_token->get(['id', 'user_id', 'date_expiry'], ['token' => $token], 1);
        if(!$token_data_query) {
            $this->response->message = 'the auth token is not valid';
        } else {
            $token_data = $token_data_query[0];
            if($token_data->date_expiry > time()) {
                $this->response->message = 'the token expired, please re-authenticate using your username and password';
                $this->authenticate_by_user_credentials();
            } else {
                $this->update_token_expiry_time($token_data->id);
                $this->get_userdata($token_data->user_id);
            }
        }
    }
    
    private function update_token_expiry_time($token_id) {
        $this->m_api_token->update(['date_expiry' => FUTURE_TOKEN_EXPIRY_DATE], ['id' => $token_id], 1);
    }
    
    private function authenticate_by_user_credentials() {
        //get the request headers
        $username = $this->input->get_request_header('Username');
        $password = $this->input->get_request_header('Password');
        //validate request headers
        if(!$username) {
            $this->response->message = 'username is required';
        } elseif(!$password) {
            $this->response->message = 'password is required';
        } else {
            //does the username exist?
            $user_query = $this->m_user->get(['id', 'password'], ['username' => $username], 1);
            if(!$user_query) {
                $this->response->message = 'user account does not exist';
            } else {
                $user = $user_query[0];
                if($password != $user->password) {
                    $this->response->message = 'user password invalid';
                } else {
                    $this->get_userdata($user->id);
                }
            }
        }
    }
    
    private function get_userdata($user_id) {
        $user_query = $this->m_user->get([], ['id' => $user_id], 1);
        if(!$user_query) {
            $this->response->message = 'user account failed to load';
        } else {
            $user = $user_query[0];
            
            //get the user meta
            $user_meta_query = $this->m_user_meta->get([], ['user_id' => $user_id], 1);
            $user_meta = $user_meta_query[0];
            
            //get the user account control
            $uac_query = $this->m_uac->get([], ['user_id' => $user_id, 'default_account' => true], 1);
            $uac = $uac_query[0];
            
            $this->userdata['id'] = $user->id;
            $this->userdata['is_admin'] = $user->is_admin;
            $this->userdata['username'] = $user->username;
            
            $this->userdata['email'] = $user_meta->email;
            $this->userdata['email_verified'] = $user_meta->email_verified;
            $this->userdata['mobile'] = $user_meta->mobile;
            $this->userdata['mobile_verified'] = $user_meta->mobile_verified;
            
            $this->userdata['account_id'] = $uac->account_id;
            $this->userdata['permissions'] = explode(',', $uac->permissions);
            
            $this->userdata['is_authenticated'] = true;
        } 
    }
}
