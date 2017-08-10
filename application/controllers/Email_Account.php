<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_Account extends MY_Controller {
    
    public function index() {}

    /*
     * Allowed inputs:
     * - host
     * - username
     * - password
     * - imap_port
     * - smtp_port
     * - enable_ssl
     * - active
     * - deleted
     * - date_created
     * - date_modified
     */
    public function get($id = null) {
        //set allowed query params, everything else is ignored
        $allowed_filter_keys = [
            'host', 'username', 'password', 'imap_port', 'smtp_port', 'enable_ssl', 'active'
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
                        $this->db_where[$allowed_filter_key] = 'MATCH (username) AGAINST ("' . $filter_value . '")';
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
        $this->response->response = $this->m_email_account->get([], $this->db_where, $id ? 1 : ($this->input->get('limit') ?? false), ($this->input->get('offset') ?? false));
    }
    
    /*
     * Allowed inputs:
     * - host
     * - username
     * - password
     * - imap_port
     * - smtp_port
     * - enable_ssl
     * - active
     */
    public function create() {
        //define allowed inputs and if it's required or not
        $allowed_inputs = [
            'host' => true, 'username' => true, 'password' => true, 'imap_port' => true, 'smtp_port' => true, 'enable_ssl' => false, 'active' => false
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
                $this->db_data[$allowed_input] = $input_value;
            }
        }

        //if validation is successful, create the new ticket
        if(!$validation_failed) {
            //create a new ticket resource
            $id = $this->m_email_account->insert($this->db_data);

            $this->db_where['id'] = $id;

            $this->response->response = $this->m_email_account->get([], $this->db_where, 1);
            $this->response->message = 'new email account created';
        }
    }
    
    /*
     * Allowed inputs:
     * - host
     * - username
     * - password
     * - imap_port
     * - smtp_port
     * - enable_ssl
     * - active
     */
    public function update($id = null) {
        //define allowed inputs and if it's required or not
        $allowed_inputs = [
            'host' => true, 'username' => true, 'password' => true, 'imap_port' => true, 'smtp_port' => true, 'enable_ssl' => false, 'active' => false
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
                $this->db_data[$allowed_input] = $input_value;
            }
        }

        //if validation is successful, create the new ticket
        if(!$validation_failed) {
            //set the record id
            $this->db_where['id'] = $id;

            //create a new ticket resource
            $this->m_email_account->update($this->db_data, $this->db_where);

            $this->response->response = $this->m_email_account->get([], $this->db_where, 1);
            $this->response->message = 'ticket updated';
        }
    }
    
    /*
     * Allowed inputs:
     * - id
     */
    public function delete($id = null) {
        $this->db_where['id'] = $id;
        $affected_records = $this->m_email_account->delete($this->db_where);
        if($affected_records) {
            $this->response->message = $affected_records . ' email account/s deleted';
        } else {
            $this->response->message = 'nothing was deleted';
        }
    }
}
