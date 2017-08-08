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

        echo 'running email cron... <br>';

        $imap_stream = imap_open("{" . $this->config->item('imap_host') . ":" . $this->config->item('imap_port') . "/imap/ssl/novalidate-cert}INBOX", $this->config->item('imap_user'), $this->config->item('imap_pass'), OP_READONLY);

        if (imap_ping($imap_stream)) {
            echo 'IMAP connection is open... <br>';

            //$message_count = imap_num_msg($imap_stream);
            //echo 'There are ' . $message_count . ' message(s) in the mailbox. <br>';

            $messages = imap_search($imap_stream, 'SINCE "' . date('j F Y', strtotime('yesterday')) . '"', SE_UID);

            if ($messages) {
                foreach ($messages as $message) {
                    $overview = imap_fetch_overview($imap_stream, $message);

                    $structure = imap_fetchstructure($imap_stream, $message);

                    //$body = imap_fetchbody($imap_stream, $message, 0); //0=headers/1=message/2=attachment

                    $parts = $this->prepare_email_parts($structure->parts);

                    foreach ($parts as $part_number => $part) {
                        //is this an attached email?
                        if ($part->ifsubtype && $part->subtype == 'RFC822') {
                            //treat it like an attachment
                            echo 'rfc822 attachment<br>';
                            $body = $this->get_part($imap_stream, $message, $part_number, $part->encoding);
                            $email_attachment_headers = imap_rfc822_parse_headers($body);
                            echo '<pre>';
                            var_dump($email_attachment_headers->subject);
                            echo '</pre>';
                            
                            //store as .eml files
                            
                            $saved_attachment = file_put_contents(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $email_attachment_headers->subject . '.eml', $body);
                            echo 'attachment ' . ($saved_attachment ? 'saved' : 'did not save') . '<br>';
                        } else {
                            
                            $part_data = $this->get_part($imap_stream, $message, $part_number, $part->encoding);
                            
                            switch ($part->type) {
                                case 0:
                                    // html or plain text email
                                    
                                    switch ($part->subtype) {
                                        case 'HTML':
                                            echo 'is html text<br>';
                                            break;
                                        case 'PLAIN':
                                            echo 'is plain text<br>';
                                            break;
                                        default:
                                            echo 'not sure<br>';
                                    }
                                    echo $part_data . '<br><br>';
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
                                    //echo 'has an attachment<br>';
                                    $filename = $this->get_part_filename($part);
                                    if ($filename) {
                                        echo 'attached file: ' . $filename . '<br>';
                                        $saved_attachment = file_put_contents(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $filename, $part_data);
                                        echo 'attachment ' . ($saved_attachment ? 'saved' : 'did not save') . '<br>';
                                    } else {
                                        echo 'is possibly another email attachment<br>';
                                    }
                                    break;
                                default:
                            }
                        }
                    }

                    echo '<pre>';
                    //print_r($parts);
                    echo '</pre>';

                    echo str_repeat('-', 24) . '<br><br>';
                }
            }

            imap_close($imap_stream);
        }
    }

    private function prepare_email_parts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {
        foreach ($messageParts as $part) {
            $flattenedParts[$prefix . $index] = $part;
            
            if ($part->ifsubtype && $part->subtype == 'RFC822') {
                return $flattenedParts;
            }
            
            if (isset($part->parts)) {
                if ($part->type == 2) {
                    $flattenedParts = $this->prepare_email_parts($part->parts, $flattenedParts, $prefix . $index . '.', 0, false);
                } elseif ($fullPrefix) {
                    $flattenedParts = $this->prepare_email_parts($part->parts, $flattenedParts, $prefix . $index . '.');
                } else {
                    $flattenedParts = $this->prepare_email_parts($part->parts, $flattenedParts, $prefix);
                }
                unset($flattenedParts[$prefix . $index]->parts);
            }
            $index++;
        }

        return $flattenedParts;
    }

    function get_part($connection, $messageNumber, $partNumber, $encoding) {
        $data = imap_fetchbody($connection, $messageNumber, $partNumber);
        switch ($encoding) {
            case 0: return $data; // 7BIT
            case 1: return $data; // 8BIT
            case 2: return $data; // BINARY
            case 3: return base64_decode($data); // BASE64
            case 4: return quoted_printable_decode($data); // QUOTED_PRINTABLE
            case 5: return $data; // OTHER
        }
    }

    function get_part_filename($part) {

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
