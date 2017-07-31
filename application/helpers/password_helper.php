<?php

/*
 * A function to calulate the best cost for a password and returns the encrypted password.
 */
if(!function_exists('encrypt_password')) {
    function encrypt_password($password) {
        $time_target = 0.05; // 50 milliseconds 
        //calculate the best cost to use for this password
        $cost = 8;
        do {
            $cost++;
            $start = microtime(true);
            $encrypted_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
            $end = microtime(true);
        } while (($end - $start) < $time_target);
        //return the encryped password
        return $encrypted_password;
    } 
}
