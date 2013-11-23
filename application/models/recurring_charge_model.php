<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* User Recurring Charges Model
* 
* @package 		Globelabs
* @subpackage 	User
* @category    	Recurring Charge
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/

class Recurring_charge_model extends MY_Model {

	function __construct()
	{
		parent::__construct();
		$this->initialize(DB_GLOBELABS,TABLE_RECURRING_CHARGES);
	}


}

/* End of file recurring_charge_model.php */
/* Location: ./system/application/models/recurring_charge_model.php */