<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Beneficiary node REST Model
* 
* @package 		Globelabs
* @subpackage 	Beneficiary
* @category    	Models
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/

class Beneficiary_model extends MY_Model {

	function __construct()
	{
		parent::__construct();
		$this->initialize(DB_GLOBELABS,TABLE_BENEFICIARIES);
	}

}

/* End of file beneficiary_model.php */
/* Location: ./system/application/models/beneficiary_model.php */