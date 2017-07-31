<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    
    protected $request_input;
    
    public $response;

    public function __construct() {
        parent::__construct();
        
        $this->request_input = json_decode($this->input->raw_input_stream);
        
        $this->response = new stdClass();
        $this->response->status = false;
        $this->response->response = [];
        $this->response->message = '';
        
        if(!(in_array(strtolower($this->router->class), ['user']) && in_array(strtolower($this->router->method), ['signup']))) {
            $this->authenticate();
        }
    }

    public function authenticate() {
        $token = $this->request_input->AuthToken;
        if($token) {
            $token_data = $this->m_api_token->get(['user_id', 'date_expiry'], ['token' => $token], 1);
            if(!$token_data) {
                $this->response->message = 'the token is not valid';
            } else {
                $token_data = $token_data[0];
                if($token_data->date_expiry > time()) {
                    $this->response->message = 'the token expired, please re-authenticate using your username and password';
                } else {
                    
                }
            }
        } else {
            
        }
    }
}
