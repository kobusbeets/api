<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Author: Kobus Beets
 * 
 * This is the base model class used to perform database actions.
 */
class MY_Model extends CI_Model {
    
    protected $table;
    
    public function __construct() {
        parent::__construct();
    }
    
    /*
     * insert a record into the database
     */
    public function insert($data = []) {
        if(empty($data)) {
            return false;
        }
        
        //set the created and modified time
        $data['date_created'] = $data['date_modified'] = time();
        
        //insert the record into the database
        $this->db->insert($this->table, $data);

        //return the insert id
        return $this->db->insert_id();
    }
    
    /*
     * get records from the database
     */
    public function get($fields = [], $where = [], $limit = false, $offset = false, $return_single = false) {
        //set the selected fields
        if(!empty($fields)) {
            $this->db->select(implode(', ', $fields));
        }
        
        //the user should only access non-deleted records unless specified otherwise
        if(!isset($where['deleted'])) {
            $where['deleted'] = false;
        }
        
        //add the custom search filter
        if(isset($where['search'])) {
            $this->db->where($where['search']);
            //remove the search key from the where clause
            unset($where['search']);
        }
        
        //set the query conditions
        if(!empty($where)) {
            $this->db->where($where);
        }
        
        //set the query limit and offset
        if(!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        
        //get the records
        $query = $this->db->get($this->table);
        
        //return the results
        return $return_single && $query->num_rows() == 1 ? $query->row() : $query->result();
    }
    
    /*
     * update a record in the database
     */
    public function update($data = [], $where = [], $limit = false) {
        //set the query conditions
        if(!empty($where)) {
            $this->db->where($where);
            //set a limit to the update query
            if($limit) {
                //limit the update query
                $this->db->limit($limit);
            }
        } else {
            //limit the updates to prevent bad things from happening
            $this->db->limit(1);
        }
        
        //update the modified time
        $data['date_modified'] = time();
        
        $this->db->update($this->table, $data);
        
        return $this->db->affected_rows();
    }
    
    /*
     * mark a record as deleted in the database
     */
    public function delete($where = []) {
        //should not run deleted query on already deleted records
        if(!isset($where['deleted'])) {
            $where['deleted'] = false;
        }
        
        $this->update([
            'deleted' => true
        ], $where);
        return $this->db->affected_rows();
    }
    
    public function count($where = []) {
        if(!isset($where['deleted'])) {
            $where['deleted'] = false;
        }
        
        return $this->db->count_all_results(DB_USER);
    }
}