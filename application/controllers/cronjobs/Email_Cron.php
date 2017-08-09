<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Email_Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->config('email');
    }

    public function index() {
        //quit if not running via cli
        if (!is_cli() && false) {
            echo 'The email cron can only be run via command line.';
            exit;
        }
        
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

            $imap_stream = imap_open('{' . $email_account->host . ':' . $email_account->imap_port . '/imap' . ($email_account->enable_ssl ? '/ssl/novalidate-cert' : '') . '}INBOX', $email_account->username, $email_account->password, OP_READONLY);

            if (imap_ping($imap_stream)) {
                echo 'connection to mailbox is open. starting to retrieve messages... <br>';
                //get emails only from today
                $messages = imap_search($imap_stream, 'SINCE "' . date('j F Y', strtotime('yesterday')) . '"', SE_UID);
                //check if there are any emails
                if ($messages) {
                    //loop through emails and process their data
                    foreach ($messages as $message) {
                        //get the headers for the current email
                        //$overview = imap_fetch_overview($imap_stream, $message);
                        
                        $headers = imap_headerinfo($imap_stream, $message);
                        
                        $email_data = [
                            'account_id' => $email_account->account_id,
                            'message_id' => $headers->message_id,
                            'subject' => $headers->subject,
                            'from' => $headers->from[0]->mailbox . '@' . $headers->from[0]->host,
                            'to' => $headers->to[0]->mailbox . '@' . $headers->to[0]->host,
                            'headers' => imap_fetchheader($imap_stream, $message)
                        ];
                        
                        //create the ticket data array
                        $ticket_data = [
                            'name' => $headers->subject,
                            'status' => TS_DEFAULT,
                            'priority' => TP_DEFAULT,
                            'account_id' => $email_account->account_id
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
                                $saved_attachment = file_put_contents($attachments_path . $email_attachment_headers->subject . '.eml', $part_data);
                                echo 'rfc822 attached email ' . ($saved_attachment ? 'saved' : 'did not save') . '...<br>';
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
                                            $saved_attachment = file_put_contents($attachments_path . $filename, $part_data);
                                            echo 'attachment ' . $filename . ' ' . ($saved_attachment ? 'saved' : 'did not save') . '...<br>';
                                        } else {
                                            // file name was not found, what is it?
                                        }
                                        break;
                                    default:
                                }
                            }
                        }

                        $ticket_id = $this->m_ticket->insert($ticket_data);
                        
                        $email_data['ticket_id'] = $ticket_id;
                        
                        $this->m_email->insert($email_data);

                        echo str_repeat('-', 24) . '<br><br>';
                    }
                }

                //close the imap connection
                imap_close($imap_stream);
                echo 'connection to mailbox closed...<br>';
            }
        }
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
