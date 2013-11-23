<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Common debugging functions
* 
* @package 		Globelabs
* @subpackage 	Debug
* @category    	Helpers
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/

/**
* uses vardump on a variable 
* 
* @param 	(mixed)   $var 		variable to be dumped
* @param 	(boolean) $do_log	write to log file
* @param 	(boolean) $is_die 	execute php DIE
**/
function debug_dump($var,$is_die = TRUE,$do_log = TRUE,$die_marker = 'xxxDEBUGxxx')
{
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
	if($do_log === TRUE) log_message('info',__METHOD__ .' dump: '.print_r($var,true));
	if($is_die === TRUE) die($die_marker);
}

/**
* uses vardump on a variable 
* 
* @param 	(mixed)   $var 		variable to be dumped
* @param 	(boolean) $do_log	write to log file
* @param 	(boolean) $is_die 	execute php DIE
**/
function debug_print($var,$is_die = TRUE,$do_log = TRUE,$die_marker = 'xxxDEBUGxxx')
{
	echo "<pre>";
	print_r($var);
	echo "</pre>";
	if($do_log === TRUE) log_message('info',__METHOD__ .' dump: '.print_r($var,true));
	if($is_die === TRUE) die($die_marker);
}

/* End of file debug_helper.php */
/* Location: ./system/application/helpers/debug_helper.php */
