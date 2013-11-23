<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cron extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('organization_model');
		$this->load->model('recurring_charge_model');
		$this->load->model('donation_model');
	}

	public function process_recurring()
	{
		$fields = array('next_charge_date' => date('Y-m-d'));
		$results = $this->recurring_charge_model->get_node_by_fields($fields,NULL,NULL);
/*
		$sql = $this->db->last_query();
		die($sql);*/
		if(array_has_value($results))
		{
			foreach($results as $result)
			{
				//debug_print($result);
				$id = $result['id'];
				$user_id = $result['user_id'];
				$organization_id = $result['organization_id'];
				$frequency = $result['frequency'];
				$amount = $result['amount'];
				$amount = (float) $amount;
				$next_charge_date = date('Y-m-d');

				$is_success = $this->donation_model->charge($user_id,$organization_id,NULL,$amount,FALSE /*don't throw exception*/);
				
				switch($frequency)
				{
					case 'day':
						$next_charge_date = date('Y-m-d', strtotime(date('Y-m-d'). ' + 1 days'));
					break;
					case 'week':
						$next_charge_date = date('Y-m-d', strtotime(date('Y-m-d'). ' + 7 week'));
					break;
					case 'month':
						$next_charge_date = date('Y-m-d', strtotime(date('Y-m-d'). ' + 1 month'));
					break;
				}

				$data = array('next_charge_date' => $next_charge_date);
				$this->recurring_charge_model->update_node($id,$data);

			}
		}
	}

	public function notify_subscribed_recurring()
	{

		$next_charge_date = date('Y-m-d', strtotime(date('Y-m-d'). ' + 1 days'));
		$fields = array('next_charge_date' => $next_charge_date);
		$results = $this->recurring_charge_model->get_node_by_fields($fields,NULL,NULL);

		if(array_has_value($results))
		{
			$globelabs_config = $this->config->item('globelabs');
			$reference_prefix = $globelabs_config['reference_prefix'];
			$this->load->library('GlobeApi');
			$globe = $this->globeapi;
			$globe->auth($globelabs_config['app_id'],
						 $globelabs_config['app_secret']
						);
			
			foreach($results as $result)
			{
				//debug_print($result);
				$user_id = $result['user_id'];
				$organization_id = $result['organization_id'];
				
				$user_node = $this->user_model->get_node_by_id($user_id);
				$globe_access_token = $user_node['globe_access_token'];
				$mobile_number = $user_node['mobile_number'];

				$organization_node = $this->organization_model->get_node_by_id($organization_id);
				$organization_name = $organization_node['name'];

				$message = "LOADICARE: Your scheduled donation for {$organization_name} would take effect tomorrow at 12am. (free)";
				$sms = $globe->sms($reference_prefix)
							 ->sendMessage($globe_access_token, $mobile_number, $message);
			}
		}
	}

}