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

class Migration_Create_email_account_table extends CI_Migration {

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
                'host' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
                ], 
                'username' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
                ], 
                'password' => [
                    'type' => 'VARCHAR',
                    'constraint' => 250
                ], 
                'imap_port' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ], 
                'smtp_port' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ], 
                'enable_ssl' => [
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
            $this->dbforge->create_table(DB_EMAIL_ACCOUNT);
        }

        public function down() {
            $this->dbforge->drop_table(DB_EMAIL_ACCOUNT);
        }
}