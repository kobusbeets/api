<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Email_Cron extends CI_Controller {
    
    private $attachment_path;

    public function __construct() {
        parent::__construct();
        
        $this->attachment_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR;
        if(!is_dir($this->attachment_path)) {
            //create the path
            mkdir($this->attachment_path, 0777, true);
        }
        
        $this->load->config('email');
    }

    public function index() {
        //quit if not running via cli
        if (!is_cli() && false) {
            echo 'The email cron can only be run via command line.';
            exit;
        }
        
        $start_time = time();
        
        //prepare the attachments path
        $attachments_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR;
        //if the attachment directory does not exist, create it.
        if(!is_dir($attachments_path)) {
            //create the path
            mkdir($attachments_path, 0777, true);
        }
        
        echo 'starting to pop mail boxes...<br>';

        //get a list of all active email accounts
        $email_accounts = $this->m_email_account->get([], ['active' => true]);
        //loop through email accounts and get their mail
        foreach ($email_accounts as $email_account) {
            echo 'popping mailbox ' . $email_account->username . '... <br>';
            
            //get saved email message ids from the database for the last day
            $message_ids = $this->get_saved_message_ids($email_account->id);

            //open the imap stream to the mailbox
            $imap_stream = imap_open('{' . $email_account->host . ':' . $email_account->imap_port . '/imap' . ($email_account->enable_ssl ? '/ssl/novalidate-cert' : '') . '}INBOX', $email_account->username, $email_account->password, OP_READONLY);

            //check if the stream is open or not
            if (imap_ping($imap_stream)) {
                echo 'connection to mailbox is open. starting to retrieve messages... <br>';
                //get emails only from today
                $messages = imap_search($imap_stream, 'SINCE "' . date('j F Y', strtotime('yesterday')) . '"', SE_UID);
                //check if there are any emails
                if ($messages) {
                    //loop through emails and process their data
                    foreach ($messages as $message) {
                        //get the headers for the current email
                        $headers = imap_fetchheader($imap_stream, $message);
                        //parse email headers
                        $parsed_headers = imap_rfc822_parse_headers($headers);
                        
                        //skip messages that was already saved
                        if(in_array($parsed_headers->message_id, $message_ids)) {
                            echo 'message already saved...<br>';
                            continue;
                        }
                        
                        //get the ticket id if it exists in the subject
                        $ticket_id = $this->get_ticket_id_from_subject($parsed_headers->subject);
                        
                        //create the email data array to store the incoming email
                        //the email_account_id is used to retrieve unique email message ids for each account
                        //see get saved email ids above
                        $email_data = [
                            'account_id' => $email_account->account_id,
                            'email_account_id' => $email_account->id,
                            'message_id' => $parsed_headers->message_id,
                            'subject' => $parsed_headers->subject,
                            'from' => $parsed_headers->from[0]->mailbox . '@' . $parsed_headers->from[0]->host,
                            'to' => $parsed_headers->to[0]->mailbox . '@' . $parsed_headers->to[0]->host,
                            'headers' => $headers
                        ];
                        
                        //create a variable to hold email attachment data
                        $email_attachments = [];
                        
                        //create the ticket data array for new tickets
                        $ticket_data = [
                            'account_id' => $email_account->account_id,
                            'name' => $parsed_headers->subject,
                            'status' => TS_DEFAULT,
                            'priority' => TP_DEFAULT,
                        ];
                        
                        //fetch the email structure
                        $structure = imap_fetchstructure($imap_stream, $message);
                        
                        //process all parts of the email
                        $parts = $this->prepare_email_parts($structure->parts);
                        
                        //loop through each part and process the data for each
                        foreach ($parts as $part_number => $part) {
                            //get the part data
                            $part_data = $this->get_part($imap_stream, $message, $part_number, $part->encoding);
                            //is this an attached email?
                            if ($part->ifsubtype && $part->subtype == 'RFC822') {
                                //get the headers for the attached email
                                $email_attachment_headers = imap_rfc822_parse_headers($part_data);
                                //save the attached email
                                $email_attachments[] = $this->save_email_attachment($email_attachment_headers->subject . '.eml', $part_data);
                            } else {
                                switch ($part->type) {
                                    case 0:
                                        switch ($part->subtype) {
                                            case 'HTML':
                                                $ticket_data['content_html'] = $email_data['content_html'] = $part_data;
                                                break;
                                            case 'PLAIN':
                                                $ticket_data['content'] = $email_data['content'] = $part_data;
                                                break;
                                        }
                                        break;
                                    case 1:
                                    case 2:
                                        // 1 = multi-part headers, 2 = attached message headers. can ignore
                                        break;
                                    case 3:
                                    case 4:
                                    case 5:
                                    case 6:
                                    case 7:
                                        // 3-7 = attachments
                                        $filename = $this->get_part_filename($part);
                                        if ($filename) {
                                            //save the attachment
                                            $email_attachments[] = $this->save_email_attachment($filename, $part_data);
                                        } else {
                                            // file name was not found, what is it?
                                        }
                                        break;
                                    default:
                                }
                            }
                        }

                        //only create ticket if it doesn't already exist
                        if(!$ticket_id) {
                            $ticket_id = $this->m_ticket->insert($ticket_data);
                        }
                        
                        //link incoming email to the ticket
                        $email_data['ticket_id'] = $ticket_id;
                        //create the email record
                        $email_id = $this->m_email->insert($email_data);
                        
                        //save attachment data in db
                        foreach($email_attachments as $email_attachment) {
                            if($email_attachment['filestat']) {
                                $this->m_email_attachment->insert([
                                    'account_id' => $email_account->account_id,
                                    'email_id' => $email_id,
                                    'name' => $email_attachment['filename'], 
                                    'path' => $email_attachment['filepath'], 
                                    'url' => $email_attachment['filelink']
                                ]);
                            }
                        }
                        echo '<pre>';
                        print_r($email_attachments);
                        echo '</pre>';

                        echo str_repeat('-', 24) . '<br><br>';
                    }
                } else {
                    echo 'there are no messages to pop...<br>';
                }

                //close the imap connection
                imap_close($imap_stream);
                echo 'connection to mailbox closed...<br>';
            } else {
                echo 'unable to ping mailbox...<br>';
            }
        }
        
        echo 'end of popping mailboxes...<br>';
        
        $end_time = time();
        
        echo ($end_time - $start_time) . ' second/s elapsed...<br>';
    }
    
    /*
     * get the ticket id from the subject
     * params:
     * * subject - the subject of the email
     */
    private function get_ticket_id_from_subject($subject) {
        $ticket_id = null;
        //parse the subejct
        preg_match("/\[TID:(\d+)\]/", $subject, $subject_data);
        if($subject_data) {
            //does the ticket exist?
            $ticket = $this->m_ticket->get([], ['id' => $subject_data[1]], 1);
            if($ticket) {
                $ticket_id = $subject_data[1];
            }
        } 
        //return the ticket id
        return $ticket_id;
    }
    
    /*
     * save email attachment to a file. 
     * params:
     * * filename - the name of the file to be saved
     * * data - the data that should be written to a file
     */
    private function save_email_attachment($filename, $data) {
        //save attachment to a file
        $attachment = file_put_contents($this->attachment_path . $filename, $data);
        //return attachment data
        return [
            'filepath' => $this->attachment_path . $filename,
            'filename' => $filename,
            'filelink' => base_url('uploads/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $filename),
            'filestat' => $attachment ? true : false
        ];
    }
    
    //get saved message ids for today
    private function get_saved_message_ids($email_account_id = null) {
        //prepare message id array
        $message_ids = [];
        //get the message id's for today
        $emails = $this->m_email->get(['message_id'], ['email_account_id' => $email_account_id, 'date_created >=' => strtotime('today')]);
        foreach($emails as $email) {
            if(!in_array($email->message_id, $message_ids)) {
                $message_ids[] = $email->message_id;
            }
        }
        return $message_ids;
    }

    //get all parts of the mail being popped and restructure it into an array
    private function prepare_email_parts($parts, $restructured_parts = array(), $prefix = '', $index = 1, $full_prefix = true) {
        foreach ($parts as $part) {
            //add the part to the array
            $restructured_parts[$prefix . $index] = $part;
            //preserve the full RFC822 email structure
            if ($part->ifsubtype && $part->subtype == 'RFC822') { } else {
                if (isset($part->parts)) {
                    if ($part->type == 2) {
                        $restructured_parts = $this->prepare_email_parts($part->parts, $restructured_parts, $prefix . $index . '.', 0, false);
                    } elseif ($full_prefix) {
                        $restructured_parts = $this->prepare_email_parts($part->parts, $restructured_parts, $prefix . $index . '.');
                    } else {
                        $restructured_parts = $this->prepare_email_parts($part->parts, $restructured_parts, $prefix);
                    }
                    unset($restructured_parts[$prefix . $index]->parts);
                }
            }
            $index++;
        }
        return $restructured_parts;
    }

    //get a message part
    private function get_part($imap_stream, $message_number, $part_number, $encoding) {
        $data = imap_fetchbody($imap_stream, $message_number, $part_number);
        switch ($encoding) {
            case 3: // BASE64
                return base64_decode($data); 
            case 4: // QUOTED_PRINTABLE
                return quoted_printable_decode($data); 
            default: // o = 7bit, 1 = 8bit, 2 = binary, 3 = other
                return $data; 
        }
    }

    //get the file name of a message part e.g. an attachment file name
    private function get_part_filename($part) {

        $filename = '';

        if ($part->ifdparameters) {
            foreach ($part->dparameters as $object) {
                if (strtolower($object->attribute) == 'filename') {
                    $filename = $object->value;
                    break;
                }
            }
        }

        if (!$filename && $part->ifparameters) {
            foreach ($part->parameters as $object) {
                if (strtolower($object->attribute) == 'name') {
                    $filename = $object->value;
                    break;
                }
            }
        }

        return $filename;
    }
}
