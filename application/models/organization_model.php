<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Organization node REST Model
* 
* @package 		Globelabs
* @subpackage 	Organization
* @category    	Models
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
* 
*/

class Organization_model extends MY_Model {

	function __construct()
	{
		parent::__construct();
		$this->initialize(DB_GLOBELABS,TABLE_ORGANIZATIONS);
	}

}

/* End of file organization_model.php */
/* Location: ./system/application/models/organization_model.php */