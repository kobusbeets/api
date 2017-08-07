<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_Cron extends CI_Controller {

    public function index() {
        //quit if not running via cli
        if(!is_cli() && false) {
            echo 'The email cron can only be run via command line.';
            exit;
        }
        
        echo 'running email cron... <br>';
        
        $imap_stream = imap_open("{mail.yashida.biz:993/imap/ssl/novalidate-cert}INBOX", "", "", OP_READONLY);
        
        if(imap_ping($imap_stream)) {
            echo 'IMAP connection is open... <br>';
            
            //$message_count = imap_num_msg($imap_stream);
            //echo 'There are ' . $message_count . ' message(s) in the mailbox. <br>';
            
            $messages = imap_search($imap_stream, 'SINCE "' . date('j F Y', strtotime('yesterday')) . '"', SE_UID);
            
            if($messages) {
                foreach($messages as $message) {
                    $overview = imap_fetch_overview($imap_stream, $message);
                    
                    $structure = imap_fetchstructure($imap_stream, $message);
                    
                    $body = imap_fetchbody($imap_stream, $message, 0); //0=headers/1=message/2=attachment
                    
                    echo '<pre>'; print_r($body); echo '</pre>';
                    
                    break;
                }
            }
        
            imap_close($imap_stream);
        }
    }
}
