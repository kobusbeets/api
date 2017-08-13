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

class Migration_Create_email_attachment_table extends CI_Migration {

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
                'email_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true
                ], 
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255
                ], 
                'path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255
                ], 
                'url' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255
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
            $this->dbforge->create_table(DB_EMAIL_ATTACHMENT);
            
            //dbforge doesn't seem to have a function to create fulltext indexing
            //$this->db->query("ALTER TABLE `" . DB_EMAIL . "` ADD FULLTEXT KEY `search` (`subject`, `content`, `content_html`, `headers`);");
        }

        public function down() {
            $this->dbforge->drop_table(DB_EMAIL_ATTACHMENT);
        }
}