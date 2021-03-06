<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class organization extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('organization_model');
	}

	function v1_list_get()
	{
		try {
			benchmark_start(__METHOD__);
			$page = $this->get('page');

			if(!has_value($page) && $page !== 0) $page = 0;
			$offset = ($page * DEFAULT_QUERY_LIMIT);
			$limit = DEFAULT_QUERY_LIMIT;

			$result = $this->organization_model->get_node_by_fields(array(),$limit,$offset);

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
			$this->set_required_fields(array('organization_id'));
			$organization_id = $this->get('organization_id');
			$result = $this->organization_model->get_node_by_id($organization_id,FALSE);
			benchmark_end(__METHOD__);
			$this->response(array('result'=>$result));
		} catch(Exception $e) {
			benchmark_end(__METHOD__);
    		$this->response(array('message' => $e->getMessage()),400);
		}
	}

}