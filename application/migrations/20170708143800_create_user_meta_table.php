<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * type = INT | VARCHAR | TEXT | BOOLEAN etc.
 * unsigned = true 
 * default = the default value 
 * null = true 
 * auto_increment = true 
 * unique = true 
 */

class Migration_Create_user_meta_table extends CI_Migration {

        public function up() {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true
                ], 
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => '255'
                ],
                'email_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => '64'
                ],
                'email_verified' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => false
                ], 
                'mobile' => [
                    'type' => 'VARCHAR',
                    'constraint' => '32'
                ],
                'mobile_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => '64'
                ],
                'mobile_verified' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => false
                ],
                'firstname' => [
                    'type' => 'VARCHAR',
                    'constraint' => '255'
                ],
                'lastname' => [
                    'type' => 'VARCHAR',
                    'constraint' => '255'
                ],
                'deleted' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => false
                ], 
                'date_created' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ], 
                'date_modified' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ]
            ]);
            //add the primary key
            $this->dbforge->add_key('id', TRUE);
            //create the table
            $this->dbforge->create_table(DB_USER_META);
        }

        public function down() {
            $this->dbforge->drop_table(DB_USER_META);
        }
}