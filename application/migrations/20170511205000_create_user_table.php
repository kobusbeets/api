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

class Migration_Create_user_table extends CI_Migration {

        public function up() {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true
                ], 
                'is_admin' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => false
                ],
                'username' => [
                    'type' => 'VARCHAR',
                    'constraint' => '250'
                ],
                'password' => [
                    'type' => 'VARCHAR',
                    'constraint' => '250'
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
            $this->dbforge->create_table(DB_USER);
        }

        public function down() {
            $this->dbforge->drop_table(DB_USER);
        }
}