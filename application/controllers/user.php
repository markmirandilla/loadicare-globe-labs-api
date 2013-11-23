<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class user extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function v1_login_post()
	{
				try {
			benchmark_start(__METHOD__);
			$this->load->library('FacebookOAuth');
			$facebook = $this->facebookoauth;
			$fb_access_token = $this->post('fb_access_token');
			$address = array();
			$latitude = NULL;
			$longitude = NULL;
			//set facebook access token
			if (has_value($fb_access_token)) $facebook->setAccessToken($fb_access_token);
			else throw new Exception('Parameter fb_access_token is missing');

	        $fb_data  	= $facebook->getAuthInfo();
	        $fb_data  	= $fb_data['profile'];
	        $fb_user_id = $fb_data['userId'];
	        $fb_email 	= $fb_data['email'];
	        
	        if(has_value($fb_user_id))
	        {
	        	if(!has_value($fb_email)) throw new Exception("Facebook email {$fb_email} is not yet verified or missing email permission");

	        	//check if the user is already an existing user through facebook id
	        	$is_fb_id_exist = $this->user_model->is_fb_id_exist($fb_user_id);

	        	//check if the user is already an existing user through primary email
	        	//NOTE: check only via email if there is no existing user via facebook id 
	        	$is_mobile_number_exist = FALSE;
	        	if($is_fb_id_exist !== TRUE) $is_mobile_number_exist = $this->user_model->is_email_exist($fb_email);
	        	
	        	if($is_mobile_number_exist === TRUE || $is_fb_id_exist === TRUE)
	        	{
	        		/*
	        		* if email already exist just return the usernode as a success
					* but always save the new facebook access_token
					*/
					$result = $this->user_model->get_node_by_fields(array('facebook_id' => $fb_user_id));
					if(!isset($result[0]))
					{
						$result = $this->user_model->get_node_by_fields(array('email' => $fb_email));	
					}
					$user_node = $result[0];
					
					//include the facebook access token in the usernode
					/*$user_node['data']['fb_access_token'] = $fb_access_token;
					$data = array();
					$data['data'] = json_encode($user_node['data']);
					$data['facebook_id'] = $fb_user_id;
					$user_node = $this->user_model->set_as_public(TRUE)->update_node($user_node['id'],$data);
					*/
					//asynchronously extend user's facebook access token to 60 days
					//$method_params = array($user_node['id'],$fb_access_token);
					//exec_background_process('user_model','async_extend_facebook_access_token','model',$method_params);

					$result = array();
					$result['result']['user'] = $user_node;
	        		benchmark_end(__METHOD__);
	        		$this->response($result);
	        	} else //register the user then return the usernode 
	        	{
	        		//generate valid username
	        		$username = $this->user_model->validate_unique_username(email_to_username($fb_email));

	        		$email 			= $fb_email;
	        		$facebook_id 	= $fb_user_id;
	        		$password 		= generate_initial_password();
	        		$fname  		= isset($fb_data['name']['givenName']) ? $fb_data['name']['givenName'] : NULL;
	        		$lname  		= isset($fb_data['name']['familyName']) ? $fb_data['name']['familyName'] : NULL;
	        		$avatar  		= isset($fb_data['photo']) ? $fb_data['photo'] : NULL;

	        		//no user address for now
	        		$latitude = NULL;
	        		$longitude = NULL;
	        		$address = NULL;

	        		$data = array();
	        		$role = NULL;
	        		$gender = NULL;
	        		$birthday = NULL;
	        		$user_node = $this->canonical_signup($email,$username,$password,$latitude,$longitude,
	        											$address,$data,$role,NULL,$fname,$lname,$avatar,
	        											$gender,$birthday,$facebook_id);

	        		//asynchronously extend user's facebook access token to 60 days
					//$method_params = array($user_node['id'],$fb_access_token);
					//exec_background_process('user_model','async_extend_facebook_access_token','model',$method_params);
	        		
	        		//$this->load->library('Email_utility');
	        		//$this->email_utility->async_send_email('email_signup',$user_node['id']);
	        		benchmark_end(__METHOD__);
	        		$result = array();
	        		$result['result']['user'] = $user_node;
	        		$this->response($result);
	        	}
	        } else {
	        	benchmark_end(__METHOD__);
	        	throw new Exception('Error getting facebook data');
	        }
    	} catch(Exception $e) {
    		benchmark_end(__METHOD__);
    		$this->response(array('message' => $e->getMessage()),400);
    	}
	}

	public function v1_details_get()
	{

	}



}