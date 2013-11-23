<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* User node REST Model
* 
* @package 		Globelabs
* @subpackage 	User
* @category    	Models
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/

class User_model extends MY_Model {

	function __construct()
	{
		parent::__construct();
		$this->initialize(DB_GLOBELABS,TABLE_USERS);
	}

	/**
	 * search for a usernode with based on email address
	 * @param 	(string)	$email 	email of the user
	 * @return 	(mixed) 	usernode if true, boolean False if no match
	 **/
	public function get_user_by_email($email)
	{
		benchmark_start(__METHOD__);
		$result = $this->get_node_by_fields(array('email' => $email));
		benchmark_end(__METHOD__);
		if(isset($result[0])) return $result[0];
		else return FALSE;	
	}

	/**
	 * Validates if the username and password is matched
	 * @param 	(string)	$email 				email of the user
	 * @param 	(string)	$password 			password of the user
	 * @param 	(boolean)	$return_usernode	option to return usernode
	 * @return 	(array) 	usernode if true, boolean False if username and password does not match
	 **/
	public function validate_user($email,$password,$return_usernode = FALSE) 
	{
		benchmark_start(__METHOD__);
		$result = $this->get_node_by_fields(array('email' => $email,'password' => md5(utf8_encode($password))));
		benchmark_end(__METHOD__);
		if(isset($result[0]) && $return_usernode === TRUE) return $result[0];
		else if(isset($result[0])) return TRUE;
		else return FALSE;
	}

	/**
	 * Validates if the userid and password is matched
	 * @param 	(string)	$userid 	userid of the user
	 * @param 	(string)	$password 	password of the user
	 * @return 	(array) 	usernode if true, boolean False if userid and password does not match
	 **/
	public function validate_user_by_user_id_and_password($userid,$password) 
	{
		benchmark_start(__METHOD__);
		$fields = array('id' => $userid,'password' => md5($password));
		$result = $this->is_node_exist_by_fields($fields);
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * Validates if the username and password is matched
	 * @param 	(string)	$user_name
	 * @param 	(string)	$user_id 	
	 * @return 	(array) 	usernode
	 **/
	public function validate_unique_username($user_name,$user_id = NULL)
	{
		benchmark_start(__METHOD__);
		$user_name_is_unique = FALSE;
		$tmp_user_name = $user_name;
		$x = 1;
        while($user_name_is_unique === FALSE) {
            $where = array();
            $where['username'] = $tmp_user_name;
            if(has_value($user_id)) $where['id !='] = $user_id;
            $result = $this->is_node_exist_by_fields($where);
            if($result === TRUE) {
            	$x = $x + 1;
                $tmp_user_name = $user_name."-{$x}";
            } else {
                $user_name_is_unique = TRUE;
            }
        }
        benchmark_end(__METHOD__);
        return $tmp_user_name;
	}

	/**
	 * Checks if an email is already existing in the userbase
	 * @param 	(string) 	$email	
	 * @return 	(boolean)
	 **/
	public function is_email_exist($email) 
	{
		benchmark_start(__METHOD__);
		$fields = array('email' => $email);
		$result = $this->is_node_exist_by_fields($fields);
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * Checks if a facebook_id is already existing in the userbase
	 * @param 	(string) 	$facebook_id	
	 * @return 	(boolean)
	 **/
	public function is_fb_id_exist($facebook_id)
	{
		benchmark_start(__METHOD__);
		$fields = array('facebook_id' => $facebook_id);
		$result = $this->is_node_exist_by_fields($fields);
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * extends and updates the user's facebook access token
	 * @param 	(string) 	$user_id	
	 * @param 	(string) 	$facebook_access_token
	 **/
	public function async_extend_facebook_access_token($user_id,$facebook_access_token)
	{
		benchmark_start(__METHOD__);
		log_message('INFO',"Extending facebook_access_token for user_id: {$user_id} | fb_access_token: {$facebook_access_token}");
		$facebook_access_token = $this->extend_facebook_access_token($facebook_access_token);
		if(has_value($facebook_access_token))
		{
			log_message('INFO',"Extended facebook_access_token for user_id: {$user_id} | fb_access_token: {$facebook_access_token}");
			$user_node = $this->user_model->get_node_by_id($user_id,FALSE);
			$user_node['data']['fb_access_token'] = $facebook_access_token;
			$data = array();
			$data['data'] = json_encode($user_node['data']);
			$user_node = $this->user_model->update_node($user_node['id'],$data);
			if(isset($user_node['data']['fb_access_token']))
			{
				log_message('INFO',"Update user facebook_access_token successful for user_id: ".$user_node['id']);
			} else {
				log_message('ERROR',"facebook_access_token has not been updated for user_id ".$user_node['id']);
			}
		} else {
			log_message('ERROR',"cannot facebook_access_token {$facebook_access_token} for user_id ".$user_node['id']);
		}
		benchmark_end(__METHOD__);
	}

	/**
	 * returns an access_token will be set to expire in 60 days, renewing the extended access token
	 * with the newly extended expiration time may or may not be the same as the previously granted 
	 * extended access_token.
	 * 
	 * @param  $existing_access_token   facebook's access token
	 * @return (string)                 extended facebook's access token with expiry of 60 days 
	 */
	public function extend_facebook_access_token($existing_access_token) {
	    benchmark_start(__METHOD__);
	    $snsKeys = $this->config->item('snsKeys');
	    $appId = $snsKeys['facebook']['appID'];
	    $appSecret = $snsKeys['facebook']['apiSecretKey'];
	    $access_token = NULL;
	    /*
	    * sample result
	    * string(140) "access_token=AAADcINZAOxm8BAKXAJ4QAruLOXePwjXPX9kvJtrJIXxjG8MMwEQcgEYhNuA9ZBpXRAkhZA9qhMkYFVjelDDNZB9BrTYLgAhovezseNO3dgZDZD&expires=5183912"
	    */
	    try {
	        $graph_url = "https://graph.facebook.com/oauth/access_token?client_id={$appId}&client_secret={$appSecret}&grant_type=fb_exchange_token&fb_exchange_token={$existing_access_token}";
	        $accessToken = @file_get_contents($graph_url);
	        parse_str($accessToken); //get the access_token param in the string and would be named $access_token
	        if(!has_value($access_token)) $access_token = $existing_access_token;
	    } catch(Exception $e) {
	        $access_token = $existing_access_token;
	    }
	    benchmark_end(__METHOD__);
	    return $access_token;
	}

}

/* End of file user_model.php */
/* Location: ./system/application/models/user_model.php */