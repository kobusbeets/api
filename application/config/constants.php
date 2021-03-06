<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//api token expiry time
defined('FUTURE_TOKEN_EXPIRY_DATE') OR define('FUTURE_TOKEN_EXPIRY_DATE', time() + (60*60*8));

//define system permissions
defined('SYSTEM_PERMISSIONS') OR define('SYSTEM_PERMISSIONS', [
    'fullaccess', 
    'can_create_user', 'can_update_user', 'can_remove_user',
    'can_create_ticket', 'can_update_ticket',
    'can_create_task', 'can_update_task', 'can_remove_task'
]);

//ticket status list
defined('TS_LIST') OR define('TS_LIST', ['open', 'inhand', 'onhold', 'closed']);
defined('TS_DEFAULT') OR define('TS_DEFAULT', 'open');

//ticket priority list
defined('TP_LIST') OR define('TP_LIST', ['low', 'medium', 'high', 'sla']);
defined('TP_DEFAULT') OR define('TP_DEFAULT', 'medium');

//ticket status - might not be used
defined('TS_OPEN') OR define('TS_OPEN', 1);
defined('TS_INHAND') OR define('TS_INHAND', 2);
defined('TS_ONHOLD') OR define('TS_ONHOLD', 3);
defined('TS_CLOSED') OR define('TS_CLOSED', 4);

//ticket priority - might not be used
defined('TP_LOW') OR define('TP_LOW', 1);
defined('TP_MEDIUM') OR define('TP_MEDIUM', 2);
defined('TP_HIGH') OR define('TP_HIGH', 3);
defined('TP_SLA') OR define('TP_SLA', 4);

//database tables
defined('DB_ACCOUNT') OR define('DB_ACCOUNT', 'account');
defined('DB_ACCOUNT_SUBSCRIPTION') OR define('DB_ACCOUNT_SUBSCRIPTION', 'account_subscription');
defined('DB_USER') OR define('DB_USER', 'user');
defined('DB_USER_META') OR define('DB_USER_META', 'user_meta');
defined('DB_USER_ACCOUNT_CONTROL') OR define('DB_USER_ACCOUNT_CONTROL', 'user_account_control');
defined('DB_API_TOKEN') OR define('DB_API_TOKEN', 'api_token');
defined('DB_TICKET') OR define('DB_TICKET', 'ticket');
defined('DB_EMAIL_ACCOUNT') OR define('DB_EMAIL_ACCOUNT', 'email_account');
defined('DB_EMAIL') OR define('DB_EMAIL', 'email');
defined('DB_EMAIL_ATTACHMENT') OR define('DB_EMAIL_ATTACHMENT', 'email_attachment');

defined('DB_NOTE') OR define('DB_NOTE', 'note');
defined('DB_TASK') OR define('DB_TASK', 'task');

defined('DB_EMAIL_RULE') OR define('DB_EMAIL_RULE', 'email_rule');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
