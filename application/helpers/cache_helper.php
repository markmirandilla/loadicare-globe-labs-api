<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Easy interface for accessing Memcached through simple_cache_model,
* simple_cache_model is a facade for Memcached
* 
* @package 		Globelabs
* @subpackage 	Cache
* @category    	Helpers
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/


/**
 * returns cached data based on $group $key params
 * 
 * @param string  $group 	is the namespace for cache key
 * @param string  $key 		is the unique key for every $group
 * @return mixed 			cache key result
 */
function get_from_cache($group, $key) {
    $CI =& get_instance();
    return $CI->simple_cache_model->get($group, $key);
}

/**
 * saves any data to cache
 * 
 * @param string  $group 				is the namespace for cache key
 * @param string  $key 					is the unique key for every $group
 * @param string  $value 				data to be cached
 * @param int  	  $expireMinutes 		number of minutes before the cached data expires
 */
function put_in_cache($group, $key, $value, $expireMinutes = NULL) {
	 $CI =& get_instance();
	if(!has_value($expireMinutes)) $expireMinutes = $CI->config->item('default_cache_life');
    $CI->simple_cache_model->put($group, $key, $value, $expireMinutes);
}

/**
 * deletes cached data based on $group $key params
 * 
 * @param string  $group 	is the namespace for cache key
 * @param string  $key 		is the unique key for every $group
 */
function delete_from_cache($group, $key) {
    $CI =& get_instance();
    $CI->simple_cache_model->delete($group, $key);
}

/* End of file cache_helper.php */
/* Location: ./system/application/helpers/cache_helper.php */