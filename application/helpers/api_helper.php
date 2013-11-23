<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Common functions related with the REST API
* 
* @package 		GlobeLabs
* @subpackage 	Api
* @category    	Helpers
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/

/**
* filters user node data to be access publicly 
* 
* @param 	(array) 	user node to be filtered
* @return 	(array) 	user node
**/
function filter_user_node($user_node)
{
	if(!has_value($user_node) || !is_array($user_node)) return $user_node;
	unset($user_node['mobile_number']);
	unset($user_node['globe_access_token']);
	unset($user_node['password']);
	unset($user_node['data']);
    unset($user_node['date_updated']);
	unset($user_node['date_created']);
	return $user_node;
}

/* End of file api_helper.php */
/* Location: ./system/application/helpers/api_helper.php */
