<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Donation node REST Model
* 
* @package 		Globelabs
* @subpackage 	Donation
* @category    	Models
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/

class Donation_model extends MY_Model {

	function __construct()
	{
		parent::__construct();
		$this->initialize(DB_GLOBELABS,TABLE_DONATIONS);
	}

	public function generate_reference_no()
	{
		$globelabs_config = $this->config->item('globelabs');
		$reference_prefix = $globelabs_config['reference_prefix'];

		$result = $this->set_node_table(TABLE_ID_STORAGE)->get_node_by_fields(array());
		$result = $result[0];
		$reference_no = $result['reference_ctr'];
		$new_reference_no = 1 + (int) $reference_no;
		$new_reference_no = str_pad($new_reference_no, 6,"0",STR_PAD_LEFT);

		$new_reference_no = "{$reference_prefix}{$new_reference_no}";
		return $new_reference_no;
	}

	public function increment_reference_no()
	{
		$result = $this->set_node_table(TABLE_ID_STORAGE)->get_node_by_fields(array());
		$result = $result[0];
		$id = $result['id'];
		$reference_no = $result['reference_ctr'];
		$new_reference_no = 1 + (int) $reference_no;
		$this->set_node_table(TABLE_ID_STORAGE)->update_node($id,array('reference_ctr'=>$new_reference_no));
		$new_reference_no = str_pad($new_reference_no, 6,"0",STR_PAD_LEFT);
		return $new_reference_no;
	}

	public function create_transaction($data)
	{
		$organization_id = $data['organization_id'];
		$this->set_node_table(TABLE_DONATIONS)->create_node($data);

		$sql = "SELECT sum(amount) as 'total_donations' from globelabs.donations where organization_id = '{$organization_id}'";
		$result = $this->db->query($sql)->row();
		$total_donations = $result->total_donations;

		$data = array();
		$data['total_funds'] = $total_donations;
		$this->set_node_table(TABLE_ORGANIZATIONS)->update_node($organization_id,$data);

	}

}

/* End of file donation_model.php */
/* Location: ./system/application/models/donation_model.php */