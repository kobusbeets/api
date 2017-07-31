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

class Migration_Create_email_rule_table extends CI_Migration {

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
                'email_account_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ], 
                'field' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
                ], 
                'operator' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
                ], 
                'value' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
                ], 
                'action' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
                ], 
                'assign_user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ], 
                'tags' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
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
            $this->dbforge->create_table(DB_EMAIL_RULE);
        }

        public function down() {
            $this->dbforge->drop_table(DB_EMAIL_RULE);
        }
}