<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * The MY_Controller super class is aimed at protecting all subclassed from unauthorised access.
 * The _remap method in the MY_Controller super class makes sure only authenticated users can proceed.
 */
class MY_Controller extends CI_Controller {

    //the request input variable
    public $request_input;
    //the user data variable used in subclasses 
    public $userdata;
    //the response object variable
    public $response;
    //limit all record to the user's default account_id
    public $db_where;
    public $db_data;

    public function __construct() {
        parent::__construct();

        //store the request input in the class
        $this->request_input = json_decode($this->input->raw_input_stream);

        //setting the user's default is authenticated status to false
        $this->userdata = [
            'is_authenticated' => false
        ];
        
        //create the response object structure
        $this->response = new stdClass();
        $this->response->status = false;
        $this->response->response = [];
        $this->response->message = '';

        //initialize the where array object
        $this->db_where = [];
        $this->db_data = [];
        
        //$this->output->enable_profiler(TRUE);
        //skip routes that does not require authentication
        if (!(in_array(strtolower($this->router->class), ['user', 'bad_request']) && in_array(strtolower($this->router->method), ['signup', 'request_not_valid']))) {
            $this->authenticate();
        }
    }

    //a method to help with getting request inputs
    public function get_input($key = null) {
        return $key && isset($this->request_input->$key) ? $this->request_input->$key : null;
    }

    //perform user authentication
    public function authenticate() {
        $token = $this->input->get_request_header('AuthToken');
        if ($token) {
            $this->authenticate_by_token($token);
        } else {
            $this->authenticate_by_user_credentials();
        }
    }

    //authenticate the user by the provided token
    private function authenticate_by_token($token) {
        $token_data_query = $this->m_api_token->get(['id', 'user_id', 'date_expiry'], ['token' => $token], 1);
        if (!$token_data_query) {
            $this->response->message = 'the auth token is not valid';
        } else {
            $token_data = $token_data_query[0];
            echo $token_data->date_expiry . '>' . time();
            if ($token_data->date_expiry > time()) {
                $this->response->message = 'the token expired, please re-authenticate using your username and password';
                $this->authenticate_by_user_credentials();
            } else {
                $this->update_token_expiry_time($token_data->id);
                $this->get_userdata($token_data->user_id);
            }
        }
    }

    //update the token expiry time
    private function update_token_expiry_time($token_id) {
        $this->m_api_token->update(['date_expiry' => FUTURE_TOKEN_EXPIRY_DATE], ['id' => $token_id], 1);
    }

    //authenticate user by the provided user credentials
    private function authenticate_by_user_credentials() {
        //get the request headers
        $username = $this->input->get_request_header('Username');
        $password = $this->input->get_request_header('Password');
        //validate request headers
        if (!$username) {
            $this->response->message = 'username is required';
        } elseif (!$password) {
            $this->response->message = 'password is required';
        } else {
            //does the username exist?
            $user_query = $this->m_user->get(['id', 'password'], ['username' => $username], 1);
            if (!$user_query) {
                $this->response->message = 'user account does not exist';
            } else {
                $user = $user_query[0];
                if ($password != $user->password) {
                    $this->response->message = 'user password invalid';
                } else {
                    $this->get_userdata($user->id);
                }
            }
        }
    }

    //get basic user data
    private function get_userdata($user_id) {
        $user_query = $this->m_user->get([], ['id' => $user_id], 1);
        if (!$user_query) {
            $this->response->message = 'user account failed to load';
        } else {
            $user = $user_query[0];

            //get the user meta
            $user_meta_query = $this->m_user_meta->get([], ['user_id' => $user_id], 1);
            $user_meta = $user_meta_query[0];

            //get the user account control
            $uac_query = $this->m_uac->get([], ['user_id' => $user_id, 'default_account' => true], 1);
            $uac = $uac_query[0];

            //user auth data
            $this->userdata['id'] = $user->id;
            $this->userdata['is_admin'] = $user->is_admin;
            $this->userdata['username'] = $user->username;

            //user meta data
            $this->userdata['email'] = $user_meta->email;
            $this->userdata['email_verified'] = $user_meta->email_verified;
            $this->userdata['mobile'] = $user_meta->mobile;
            $this->userdata['mobile_verified'] = $user_meta->mobile_verified;

            //user access control data
            $this->userdata['account_id'] = $uac->account_id;
            $this->userdata['permissions'] = explode(',', $uac->permissions);

            //limit all user queries to the user's default account_id
            $this->db_where['account_id'] = $this->userdata['account_id'];
            $this->db_data['account_id'] = $this->userdata['account_id'];

            //set the user as authenticated
            $this->userdata['is_authenticated'] = true;
        }
    }

    //make sure the user is authenticated before making any api function calls
    //refactor the code in the _remap function.
    public function _remap($method, $params = array()) {
        //a flag to call a function
        $call_user_function = false;
        if (!(in_array(strtolower($this->router->class), ['user', 'bad_request']) && in_array(strtolower($this->router->method), ['signup', 'request_not_valid']))) {
            if($this->userdata['is_authenticated']) {
                $call_user_function = true;
            } else {
                $this->response->message = 'not authenticated';
            }
        } else {
            $call_user_function = true;
        }
        
        if($call_user_function) {
            if (method_exists($this, $method)) {
                return call_user_func_array(array($this, $method), $params);
            } else {
                $this->response->message = 'could not route your request';
            }
        }
    }

}
