<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class donate extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('organization_model');
		$this->load->model('donation_model');
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

			$this->donation_model->charge($user_id,$organization_id,$beneficiary_id,$amount);

			benchmark_end(__METHOD__);
			$this->response(array('status' => API_STATUS_OK));
		} catch(Exception $e) {
			benchmark_end(__METHOD__);
    		$this->response(array('message' => $e->getMessage()),400);
		}
	}

}