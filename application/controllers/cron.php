<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cron extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('recurring_charge_model');
		$this->load->model('donation_model');
	}

	public function process_recurring()
	{
		$fields = array('next_charge_date' => date('Y-m-d'));
		$results = $this->recurring_charge_model->get_node_by_fields($fields,NULL,NULL);
		if(array_has_value($result))
		{
			foreach($results as $result)
			{
				$id = $result['id'];
				$user_id = $result['user_id'];
				$organization_id = $result['organization_id'];
				$amount = $result['amount'];
				$next_charge_date = date('Y-m-d');

				$this->donation_model->charge($user_id,$organization_id,NULL,$amount,FALSE /*don't throw exception*/);

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
		
	}

}