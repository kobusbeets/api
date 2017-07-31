<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('send_email')) {
    function send_email($to, $subject, $message, $attachments = []) {
        //get the codeigniter instance
        $ci =& get_instance();
        //load the email config file
        $ci->config->load('email');
        
        $ci->email->from($ci->config->item('smtp_user'), $ci->config->item('smtp_user_name'));
        $ci->email->to($to);

        $ci->email->subject($subject);
        $ci->email->message($message);

        //add attachments to this email
        if(!empty($attachments)) {
            foreach($attachments as $attachment) {
                $ci->email->attach($attachment);
            }
        }
        
        return $ci->email->send();
    }
}
