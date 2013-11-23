<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Common functions such as random numbers, string manipulations/validations
* 
* @package 		Globelabs
* @subpackage 	Common
* @category    	Helpers
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/

/**
* converts an email address to a valid username 
* 
* @param    $email      email address string
* @return   (String)    parsed email
**/
function email_to_username($email) 
{
    $userName = explode('@',$email);
    if(!isset($userName[0])) return NULL;
    $emailChars = array('.',' ','_','+');
    $userName = str_replace($emailChars,'-',trim($userName[0]));
    return $userName;
}

/**
* converts an email address to a valid username 
* 
* @param    (objecct)   $email - email address string
* @return   (String)    Display name
**/
function get_display_name($user_node,$include_surname = FALSE)
{
    if(is_object($user_node)) $user_node = object_to_array($user_node);
    $display_name = $user_node['username'];
    if(isset($user_node['fname']) && $include_surname === FALSE)
    {
        $display_name = $user_node['fname'];
    } else if(isset($user_node['fname']) && $include_surname === TRUE)
    {
        $display_name = $user_node['fname'];
        if(isset($user_node['lname'])) $display_name = $display_name . ' ' . $user_node['lname'];
    }
    return $display_name;
}

/**
* Generates x number of digits
*
* USAGE:
* 
* $a = random_x_digits(5);
* $b = random_x_digits(3);
* 
* echo 'Result A: '.$a;
* echo 'Result B: '.$b;
*
* Result A: 45785
* Result B: 684
* 
* @param 	$digits		number of digits to be returned
* @return 	(int) 		random x number of digits
**/
function random_x_digits($digits = 5) 
{
	return rand(pow(10, $digits-1), pow(10, $digits)-1);	
}

/**
* returns a random letter
* 
* @param    (int)       $length         length of the random character string to be returned
* @param    (boolean)   $upper_case     flag if the characters are in uppercase
* @return   (string)    random character string
**/
function random_char($length = 5,$upper_case = FALSE)
{
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    if($upper_case === TRUE) strtoupper($string);
    else return $string;
}

/**
* returns a random alphanumeric characters
* 
* @param    (int)       $length     length of the random alphanumeric string to be returned
* @return   (string)    random alphanumeric character string
**/
function random_aphanumeric($length = 5)
{
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}

/**
* check variable is empty
* 
* @param 	$var		variable to be analyzed
* @return 	(boolean)
**/
function has_value($var)
{
	if(!isset($var))
	{
		return FALSE;
	} else
	{
        if(is_null($var) || empty($var))
        {
            return FALSE;
        } else
        {
            return TRUE;
        }
	}
}

/**
* checks if variable is a valid array and not empty
* 
* @param    $arr        array variable to be analyzed
* @return   (boolean)
**/
function array_has_value($arr)
{
    if(!isset($arr) && !is_array($arr))
    {
        return FALSE;
    } else
    {
        if(count($arr) === 0 || empty($arr))
        {
            return FALSE;
        } else
        {
            return TRUE;
        }
    }
}

/**
* check variable is numeric and has value
* 
* @param    $var        variable to be analyzed
* @return   (boolean)
**/
function numeric_has_value($var)
{
    if(isset($var) && is_numeric($var))
    {
        return TRUE;
    } else
    {
        return FALSE;
    }
}

/**
* changes html br tag to next line
* 
* @param    (string)    $text   string to be parsed
* @return   (boolean)
**/
function br2nl($text)
{
    return preg_replace('#<br\s*?/?>#i', "\n", $text);
}

/**
* returns null if the variable is empty
* 
* @param    $var        variable to be analyzed
* @return   (mixed)
**/
function assess_variable_value($var)
{
    return (has_value($var) ? $var : NULL);
}

/**
* returns a random set of 50 characters password
* 
* @return 	(char)
**/
function generate_initial_password() 
{
    return md5("dummy_93ah".rand()."n093".rand()."0ei3h!n0".rand()."293k".rand()."in3h");
}

/**
* fix email string when fetching from query string by adding + in spaces
* 
* @return   string  email
**/
function safe_email($email)
{
    if(isset($email)) $email = str_replace(' ', '+', trim($email));
    return $email;
} 

/**
* starting point for benchmarking a function
* 
* @param $prefix 	unique identifier for the benchmark e.g. using __METHOD__ as a prefix
* @param $doLog 	boolean flag to write to log files
**/
function benchmark_start($prefix, $doLog = TRUE) {
    $CI =& get_instance();
    $CI->_alreadyLoggedOnce = FALSE;
        $prefix = $CI->router->class . '.' . $CI->router->method . '.' . $prefix;
        $CI->benchmark->mark("${prefix}_start");
        if ($doLog) benchmark_log_raw("{ $prefix start ", 0, 2);
}

/**
* ending point for benchmarking a function
* 
* @param $prefix    unique identifier for the benchmark e.g. using __METHOD__ as a prefix
* @param $doLog     boolean flag to write to log files
**/
function benchmark_end($prefix, $doLog = TRUE) {
    $CI =& get_instance();
        $prefix = $CI->router->class . '.' . $CI->router->method . '.' . $prefix;
        $CI->benchmark->mark("${prefix}_end");
        if ($doLog) benchmark_log_raw("} $prefix end (" .  $CI->benchmark->elapsed_time("${prefix}_start", "${prefix}_end") . ' seconds)', -2, 0);
}

/**
* function to log in benchmark
* 
* @param $message    unique identifier for the benchmark e.g. using __METHOD__ as a prefix
* @param $preDelta   prefix indention
* @param $postDelta  postfix indention
**/
function benchmark_log($message, $preDelta = 0, $postDelta = 0) {
    benchmark_log_raw(">> $message", $preDelta, $postDelta);
}

/**
* function to log in benchmark
* 
* @param $message    unique identifier for the benchmark e.g. using __METHOD__ as a prefix
* @param $preDelta   prefix indention
* @param $postDelta  postfix indention
**/
function benchmark_log_raw($message, $preDelta = 0, $postDelta = 0) {
    $CI =& get_instance();
    if (!isset($CI->_benchmark_indent)) $CI->_benchmark_indent = 0;
    if (!isset($CI->_benchmark_spaces)) $CI->_benchmark_spaces = '                                                  ';
    $CI->_benchmark_indent += $preDelta;
    //$ip = $CI->utils->get_client_ip();
    // Make sure there is a controller id for thread context
    if (empty($CI->_cid)) $CI->_cid = rand(1000,9999);
    log_message('info', $CI->_cid . ': ' . substr($CI->_benchmark_spaces, 0, $CI->_benchmark_indent) . $message);
    $CI->_benchmark_indent += $postDelta;
    if (isset($CI->_alreadyLoggedOnce) && !$CI->_alreadyLoggedOnce) {
        !$CI->_alreadyLoggedOnce = TRUE;
        //benchmark_log('URL is ' . $_SERVER['PATH_INFO']);
    }
}

/**
* Create a folder if it doesn't already exist
* 
* @param (string)   $folder_with_path   folder name with path
**/
function create_dir_if_not_exist($folder_with_path)
{
    if (!is_dir($folder_with_path)) {
        log_message('INFO',__METHOD__ . " creating {$folder_with_path} directory ");
        mkdir($folder_with_path,0777);
    } else {
        log_message('INFO',__METHOD__ . " {$folder_with_path} directory already exist and would not be re-created");
    }
}

/**
* recursively converts object to array
* 
* @param (object)   $data   object to be converted to array
* @return (object)
**/
function object_to_array($data)
{
    if(is_array($data) || is_object($data)) {
        $result = array();
        foreach($data as $key => $value) {
            $result[$key] = object_to_array($value);
        }
        return $result;
    }
    return $data;
}

/**
* get attribute of xml
* 
* @param (string)   $object     xml object
* @param (string)   $attribute  attribute name
* @return (string)
**/
function get_xml_attribute($object, $attribute)
{
    if(isset($object[$attribute])) return (string) $object[$attribute];
}

/**
* searches a specific array item based on key
* 
* @param (string)   $item           the needle
* @param (string)   $array_key      key of the array to be compared with the needle
* @param (string)   $array_items    the haystak
* @return (array)
**/
function get_item_from_array($item,$array_key,$array_items)
{
    if(!array_has_value($array_items)) return array();
    if(!has_value($item)) return array();
    $array_val = array();
    foreach($array_items as $array_item)
    {
        if($array_item[$array_key] == $item)
        {
            $array_val = $array_item;
            break;
        }
    }
    return $array_val;
}

/**
* searches items from array based on key
* 
* @param (string)   $item           the needle
* @param (string)   $array_key      key of the array to be compared with the needle
* @param (string)   $array_items    the haystak
* @return (array)
**/
function get_items_from_array($item,$array_key,$array_items)
{
    if(!array_has_value($array_items)) return array();
    if(!has_value($item)) return array();
    $array_val = array();
    foreach($array_items as $array_item)
    {
        if($array_item[$array_key] == $item)
        {
            $array_val[] = $array_item;
        }
    }
    return $array_val;
}

/**
* converts the mimetype to file extension name
* 
* @param (string)   $mime_type   mime type
* @return (string)  file extension name
**/
function image_mime_type_to_extension($mime_type)
{
    switch($mime_type)
    {
        case "image/gif":
            return "gif";
        break;
        case "image/jpeg":
        case "image/jpg":
        case "image/jpe":
            return "jpg";
        break;
        case "image/png":
            return "png";
        break;
        default:
            return NULL;
    }
}

/**
* formats string parsed from csv 
* 
* @param    (string)   $str
* @return   (string)   string converted to lower case and removed white spacing
**/
function format_csv_string($str)
{
    return strtolower(trim($str));
}

/**
* converts (NULL) string to PHP NULL 
* 
* @param    (string)   $str
* @return   (string)   parsed string
**/
function format_csv_null($str)
{
    if($str === '(NULL)') return NULL;
    else return trim($str);
}

/**
* removes duplicate <br /> html tags 
* 
* @param    (string)   $str
* @return   (string)   parsed string
**/
function remove_duplicate_br_tag($str)
{
    if(has_value($str))
    {
        $str = preg_replace('#<br />(\s*<br />)+#', '<br />', $str);
    }
    return $str;
}

/**
* formats mm/dd/yyyy to unix timestamp 
* 
* @param    (string)   $date
* @return   (integer)  unix timestamp
**/
function mmddyyyy_to_timestamp($date)
{
    $date = str_replace('-', '/', $date);
    list($month, $day, $year) = explode('/', $date); 
    $timeStamp = mktime(0, 0, 0, $month, $day, $year); 
    return $timeStamp;
}

/**
* returns YYYYMMDDHHMMSSUUUU 
* 
* @return   (string)  timestamp
**/
function signature_timestamp()
{
    $format='YmdHis';
    $microtime = microtime(true);
    list($unix_time,$microseconds) = explode('.', $microtime);
    return date($format,$unix_time) . $microseconds;
}

/**
* returns the appropriate error message for the file upload error code 
* 
* @param    (integer)   $error_code
* @return   (string)    error message
**/
function  get_upload_error_message($error_code) {
    $error_message = NULL;
    switch($error_code) {
        case 0: $error_message = NULL; break;
        case 1: $error_message = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                break;
        case 2: $error_message = "The uploaded file exceeds the File size limit of 4MB";
                break;
        case 3: $error_message = "The uploaded file was only partially uploaded";
                break;
        case 4: $error_message = "No file was uploaded";
                break;
        case 6: $error_message = "Missing a temporary folder";
                break;
        case 7: $error_message = "Failed to write file to disk";
                break;
        case 8: $error_message = "A PHP extension stopped the file upload";
                break;
        default:
        $error = "Unkown Error encountered while uploading the file";
    }
    return $error_message;
}

/**
 * Simple implementation of hmac sha1
 *
 * @param   (string)    $key          hash secret key
 * @param   (string)    $data         string to hash
 * @return  (string)    hash
 */
function hmac_sha1($key, $data)
{
    // Adjust key to exactly 64 bytes
    if (strlen($key) > 64) {
        $key = str_pad(sha1($key, true), 64, chr(0));
    }
    if (strlen($key) < 64) {
        $key = str_pad($key, 64, chr(0));
    }

    // Outter and Inner pad
    $opad = str_repeat(chr(0x5C), 64);
    $ipad = str_repeat(chr(0x36), 64);

    // Xor key with opad & ipad
    for ($i = 0; $i < strlen($key); $i++) {
        $opad[$i] = $opad[$i] ^ $key[$i];
        $ipad[$i] = $ipad[$i] ^ $key[$i];
    }

    return sha1($opad.sha1($ipad.$data, true));
}

/**
 * signs a url parameter with hmac_sha1
 *
 * @param   (array)    $query_string    query string parameter converted to array
 * @param   (string)   $signature       hashed signature string to be compared
 * @param   (string)   $hash_key_secret hash secret key
 * @return  (string)   hash
 * @throws  exception when signature and hashed query_string is not equal or signature is missing
 **/
function validate_hash($query_string,$signature,$hash_key_secret = NULL)
{
    if(!array_has_value($query_string)) return NULL;
    if(!has_value($signature)) throw new exception('invalid signature');
    //remove the signature parameter
    $signature_hash = (isset($query_string['signature']) ? $query_string['signature'] : NULL);
    if(isset($query_string['signature'])) unset($query_string['signature']);
    //if(isset($query_string['access_token'])) unset($query_string['access_token']);
    //sort the query string key in ascending order
    ksort($query_string);
    //convert the query string array values to a single string
    $str_to_be_hashed = NULL;
    foreach ($query_string as $key => $val) {
        $str_to_be_hashed .= $val;
    }
    if(!has_value($hash_key_secret))
    {
        $CI =& get_instance();
        $hash_key_secret = $CI->config->item('gdeals_hash_secret');
    }
    $hashed_query_string = hmac_sha1($hash_key_secret, $str_to_be_hashed);
    if($hashed_query_string !== $signature) 
    {
        if(ENVIRONMENT === 'development')
        {
            throw new exception("invalid signature. Correct signature is {$hashed_query_string} compared to {$signature}. concatinated_query_string is {$str_to_be_hashed} secret {$hash_key_secret} query_string: ".print_r($query_string,TRUE));
        } else
        {
            throw new exception('invalid signature');
        }
    }
}

/**
 * signs a url parameter with hmac_sha1
 *
 * @param   (array)    $params    array where values would be hashed
 **/
function generate_singature($params)
{
    $CI =& get_instance();
    $hash_key_secret = $CI->config->item('gdeals_hash_secret');

    ksort($params);
    //convert the query string array values to a single string
    $str_to_be_hashed = NULL;
    foreach ($params as $key => $val) {
        $str_to_be_hashed .= $val;
    }
    $hash_string = hmac_sha1($hash_key_secret, $str_to_be_hashed);
    return $hash_string;
}

/**
 * parses a mobile number to a valid globe number
 *
 * @param   (string)    $mobile_number      
 * @return  (string)    valid mobile number
 * @throws exception when mobile number has an invalid format
 **/
function parse_mobile_number($mobile_number)
{
    if(!has_value($mobile_number)) throw new exception('mobile_number cannot be empty!');
    //remove non numeric characters
    $mobile_number = preg_replace("/[^0-9,.+-]/", "", $mobile_number);

    //get the last 10 characters of the mobile number
    $mobile_number = substr($mobile_number, -10);

    if(strlen($mobile_number) < 10 || 
        strlen($mobile_number) > 10) throw new exception('invalid mobile_number length!');
    validate_globe_number($mobile_number);
    return $mobile_number;
}

/**
 * validates if a mobile number is a valid globe number
 *
 * @param   (string)    $mobile_number  10 digit mobile number
 * @throws exception when mobile number is not a valid globe number
 **/
function validate_globe_number($globe_number)
{
    benchmark_start(__METHOD__);
    $exception_error_message = 'Invalid globe/TM number';
    $CI =& get_instance();
    if(strlen($globe_number) !== 10) throw new Exception($exception_error_message);
   
    $mobile_number_prefix = substr($globe_number, 0, 3);
    $result = $CI->db->get_where(TABLE_MOBILE_PREFIX_NUMBERS, array('prefix' => $mobile_number_prefix));
    if($result->num_rows === 0) throw new Exception($exception_error_message); 
    benchmark_end(__METHOD__);
}

function redis_message_encode($message)
{
    return str_replace(' ', '_', $message);
}

function redis_message_decode($message)
{
    return str_replace('_', ' ', $message);
}

/* End of file common_helper.php */
/* Location: ./system/application/helpers/common_helper.php */