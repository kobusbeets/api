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

class Migration_Create_ticket_table extends CI_Migration {

        public function up() {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true
                ], 
                'account_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ], 
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
                ], 
                'content' => [
                    'type' => 'TEXT'
                ], 
                'assigned_user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'unsigned' => true
                ],
                'priority' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'unsigned' => true
                ],
                'read' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => false
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
            $this->dbforge->add_key('id', true);
            //create the table
            $this->dbforge->create_table(DB_TICKET);
            
            //ALTER TABLE `ticket` ADD FULLTEXT KEY `search` (`name`,`content`);
        }

        public function down() {
            $this->dbforge->drop_table(DB_TICKET);
        }
}