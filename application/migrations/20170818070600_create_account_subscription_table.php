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

class Migration_Create_account_subscription_table extends CI_Migration {

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
                'user_limit' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => false,
                    'default' => 1
                ],
                'amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'unsigned' => true,
                    'null' => false,
                    'default' => 0.00
                ],
                'date_expiry' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ], 
                'automatic_payment' => [
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
            $this->dbforge->create_table(DB_ACCOUNT_SUBSCRIPTION);
        }

        public function down() {
            $this->dbforge->drop_table(DB_ACCOUNT_SUBSCRIPTION);
        }
}