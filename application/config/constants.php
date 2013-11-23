<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/*
|--------------------------------------------------------------------------
| API Status message
|--------------------------------------------------------------------------
|
| Status message for API
|
*/
define('API_STATUS_OK', 'Ok');
define('API_STATUS_ERROR', 	'Error');

/*
|--------------------------------------------------------------------------
| Push Notification Constants
|--------------------------------------------------------------------------
|
| These are constants for perkmeapp
|
*/
define('DEFAULT_QUERY_OFFSET', 0);
define('DEFAULT_QUERY_LIMIT', 10);

/*
|--------------------------------------------------------------------------
| Database Constants
|--------------------------------------------------------------------------
|
| Constants for database names associated with perkmeapp
|
*/
define('DB_GLOBELABS', 'globelabs');

/*
|--------------------------------------------------------------------------
| Table Constants
|--------------------------------------------------------------------------
|
| Constants for table names associated with perkmeapp
|
*/
define('TABLE_USERS', 'users');
define('TABLE_DONATIONS', 'donations');
define('TABLE_ID_STORAGE', 'id_storage');
define('TABLE_ORGANIZATIONS', 'organizations');
define('TABLE_RECURRING_CHARGES', 'recurring_charges');

/* End of file constants.php */
/* Location: ./application/config/constants.php */
