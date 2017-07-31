<?php

class API_Output {
    
    public function __construct() {
    }
    
    public function set_output() {
        //get the codeigniter instance
        $ci =& get_instance();
        
        //make sure the controller outputs the response and stops all further execution when an API controller is called
        if(get_parent_class($ci->router->class) === "MY_Controller") {
        
            //handle CORS for local development
            if(ENVIRONMENT === 'development') {
                if (strtolower($_SERVER['REQUEST_METHOD']) === 'options') {
                    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
                    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
                    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type, Authorization, UserName, UserPassword, AuthToken');
                    header('Access-Control-Allow-Credentials: true');
                    exit;
               }
            }
            
            //set the output headers
            $ci->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($ci->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();

            //stop script execution
            exit;
        }
    }
}