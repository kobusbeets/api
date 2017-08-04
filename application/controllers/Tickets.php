<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends MY_Controller {
    
    public function index() {}

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
        //define allowed inputs and if it's required or not
        $allowed_inputs = [
            'name' => true, 'content' => true, 'status' => false, 'priority' => false, 'assigned_user_id' => false, 'read' => false
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
            if($input_value) {
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
            //create a new ticket resource
            $id = $this->m_ticket->insert($this->db_data);
            
            $this->db_where['id'] = $id;
            
            $this->response->response = $this->m_ticket->get([], $this->db_where, 1);
            $this->response->message = 'new ticket created';
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
        
    }
    
    /*
     * Allowed inputs:
     * - id
     */
    public function delete($id = null) {
        
    }
}
