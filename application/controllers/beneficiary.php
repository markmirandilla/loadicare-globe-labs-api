<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class beneficiary extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('beneficiary_model');
	}

	function v1_list_get()
	{
		try {
			benchmark_start(__METHOD__);
			$this->set_required_fields(array('organization_id'));
			$organization_id = $this->get('organization_id');
			$page = $this->get('page');

			if(!has_value($page) && $page !== 0) $page = 0;
			$offset = ($page * DEFAULT_QUERY_LIMIT);
			$limit = DEFAULT_QUERY_LIMIT;

			$fields = array('organization_id' => $organization_id);
			$result = $this->beneficiary_model->get_node_by_fields($fields,$limit,$offset);

			benchmark_end(__METHOD__);
			$this->response(array('result'=>$result));
		} catch(Exception $e) {
			benchmark_end(__METHOD__);
    		$this->response(array('message' => $e->getMessage()),400);
		}
	}

	function v1_detail_get()
	{
		try {
			benchmark_start(__METHOD__);
			$this->set_required_fields(array('beneficiary_id'));
			$beneficiary_id = $this->get('beneficiary_id');
			$result = $this->beneficiary_model->get_node_by_id($beneficiary_id);
			benchmark_end(__METHOD__);
			$this->response(array('result'=>$result));
		} catch(Exception $e) {
			benchmark_end(__METHOD__);
    		$this->response(array('message' => $e->getMessage()),400);
		}
	}

}