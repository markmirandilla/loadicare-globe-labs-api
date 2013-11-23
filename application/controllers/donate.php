<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class donate extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('organization_model');
	}

	function v1_charge_post()
	{
		try {
			benchmark_start(__METHOD__);
			$this->set_required_fields(array('user_id','organization_id'));

			$user_id		 = $this->post('user_id');
			$organization_id = $this->post('organization_id');
			$beneficiary_id  = $this->post('beneficiary_id');
			$amount 		 = $this->post('amount');
			$amount 		 = (float) $amount;

			$this->load->model('donation_model');
			$this->load->model('organization_model');

			$globelabs_config = $this->config->item('globelabs');
			$reference_prefix = $globelabs_config['reference_prefix'];
			$this->load->library('GlobeApi');
			$globe = $this->globeapi;
			$auth = $globe->auth(
					    $globelabs_config['app_id'],
					    $globelabs_config['app_secret']
					);

			//get user node
			$user_node = $this->user_model->get_node_by_id($user_id);
			$globe_access_token = $user_node['globe_access_token'];
			$mobile_number = $user_node['mobile_number'];

			//get organization node
			$organization_node = $this->organization_model->get_node_by_id($organization_id);
			$organization_name = $organization_node['name'];

			$reference_no = $this->donation_model->generate_reference_no();

			$charge = $globe->payment(
						    $globe_access_token,
						    $mobile_number
							);
			$charge->description = "Donation for {$organization_name}";
			$response = $charge->charge(
							    $amount,
							    $reference_no
							);
			if(isset($response['error'])) throw new Exception($response['error']);

			$this->donation_model->increment_reference_no();
			$server_reference_code = $response['amountTransaction']['serverReferenceCode'];

			$data = array('reference_no' => $reference_no,
					'organization_id' 	 => $organization_id,		
					'beneficiary_id' 	 => $beneficiary_id,
					'user_id' 			 => $user_id,
					'mobile_no' 		 => $mobile_number,
					'amount' 			 => $amount,
					'server_reference_code' => $server_reference_code);
			$this->donation_model->create_transaction($data);

			benchmark_end(__METHOD__);
			$this->response(array('status' => API_STATUS_OK));
		} catch(Exception $e) {
			benchmark_end(__METHOD__);
    		$this->response(array('message' => $e->getMessage()),400);
		}
	}

}