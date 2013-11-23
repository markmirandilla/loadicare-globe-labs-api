<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Canonical Model for nodes
* 
* @package 		GlobeLabs
* @subpackage 	MY_Model
* @category    	Model
* @author 		Mark Mirandilla | markmirandilla.com | mark.mirandilla@gmail.com
* @version 		Version 1.0
*/
class MY_Model extends CI_Model {

    protected $mDatabase 	= DB_GLOBELABS; //name of the database
    protected $mTable 		= NULL; 		//table name initialized in initialize method
    protected $cache_group  = NULL; 		//cache group for memcache
    protected $is_ignore 	= FALSE;		//flag for INSERT IGNORE sql statement
    public 	  $is_public	= FALSE; 		//Flag for displaying the node for public

    /**
	 * constructor
	 **/
    public function __construct()
    {
    	parent::__construct();
    }

     /**
	 * initialize cache key,database name and table name
	 * 
	 * @param 	(string) 	$database 	database name
	 * @param 	(string) 	$table 		table name
	 **/
    protected function initialize($database, $table = NULL)
    {
        $this->mDatabase = $database;
        if(has_value($table))
        {
        	$this->mTable = $table;
        	$this->cache_group = "{$database}.{$table}";
    	}
    }

    /**
	 * sets the default table
	 * 
	 * @param 	(string) 	$table 	table name
	 * @return 	(object) 	class object
	 **/
    public function set_node_table($table)
    {
    	if(!has_value($table)) return $this;
    	$this->mTable = $table;
    	return $this;
    }

    /**
	 * sets the flag for displaying allowed data to the public
	 * 
	 * @param 	(boolean) 	$set_as_public
	 * @return 	(object) 	class object
	 **/
    public function set_as_public($set_as_public = FALSE)
    {
    	$this->is_public = $set_as_public;
    	return $this;
    }

    public function set_insert_ignore()
    {
    	$this->is_ignore = TRUE;
    	return $this;
    }

    /**
	 * perform batch insert
	 * 
	 * @param 	(array) 	$data 	array of records to be created
	 * @param 	(string) 	table name
	 **/
	public function batch_create_node($data,$table = NULL)
	{
		benchmark_start(__METHOD__);
		if(!has_value($table)) $table = $this->mTable;
		$insert_data = array();
		foreach($data as $datum)
		{
			//generate a node id
			$unixtimestamp = time();
			$datum['id'] 		  	= $this->utils->uuid();
			$datum['date_created'] 	= $unixtimestamp;
			$datum['date_updated'] 	= $unixtimestamp;
			$insert_data[] = $datum;
		}
		if(!empty($insert_data))
		{
			$this->db->insert_batch($table, $insert_data);
			//$sql = $this->db->last_query();
			//die($sql);
		} else {
			log_message('error','No batch insert executed '.print_r($data,TRUE));
		}
		benchmark_end(__METHOD__);
	}

    /**
	 * inserts the node to the nodes table
	 * 
	 * @param 	(array) 	$data 	array of node
	 * @param 	(string) 	$table 	table name where the node would be created
	 * @return 	(array) 	created node
	 **/
	public function create_node($data,$table = NULL)
	{
		benchmark_start(__METHOD__);
		if(!has_value($table)) $table = $this->mTable;
		$unixtimestamp = time();
		//generate a node id
		$data['id'] 		  = $this->utils->uuid();
		$data['date_created'] = isset($data['date_created']) ? $data['date_created'] : $unixtimestamp;
		$data['date_updated'] = isset($data['date_updated']) ? $data['date_updated'] : $unixtimestamp;

		//set geospatial point column
		if(isset($data['latitude']) && isset($data['longitude']))
		{
			$this->db->set("geom","geomfromtext('POINT(".$data['latitude']." ".$data['longitude'].")', 4326)",FALSE);
		}
		log_message('info','Params: '.print_r($data,TRUE));
		if($this->is_ignore === TRUE) $this->db->ignore();
		$this->db->insert($table, $data);

		$orig_table = $this->mTable;
		$node = $this->set_node_table($table)->get_node_by_id($data['id']);
		$this->set_node_table($orig_table); //return to the original default table
		log_message('info','Created node: '.print_r($data,TRUE));
		benchmark_end(__METHOD__);
		return $node;
	}

	/**
	 * updates the node from the node table
	 * 
	 * @param 	(string) 	$node_id 	node_id of the node to be updated
	 * @param 	(array) 	$data 		array of node
	 * @return 	(array) 	updated node
	 **/
	public function update_node($node_id,$data,$table = NULL)
	{
		benchmark_start(__METHOD__);
		if(!has_value($table)) $table = $this->mTable;
		$data['date_updated'] = time();
		log_message('info','Params: '.print_r($data,TRUE) ." node_id: {$node_id}");

		//set geospatial point column
		if(isset($data['latitude']) && isset($data['longitude']))
		{
			$this->db->set('geom','geomfromtext("POINT('.$data['latitude'].' '.$data['longitude'].')")',FALSE);
		}
		
		$this->db->where('id', $node_id)
				 ->update($table, $data);
		$this->_break_node_cache($node_id);
		$orig_table = $this->mTable;
		$node = $this->set_node_table($table)->get_node_by_id($node_id);
		$this->set_node_table($orig_table); //return to the original default table
		log_message('info','Updated node: '.print_r($data,TRUE));
		benchmark_end(__METHOD__);
		return $node;
	}

	/**
	 * updates the node from the node table based on a condition
	 * 
	 * @param 	(string) 	$node_id 	node_id of the node to be updated
	 * @param 	(array) 	$data 		array of node
	 * @param 	(array) 	$filters 	array of conditions for where clause
	 * @return 	(array) 	updated node
	 **/
	public function update_node_by_fields($data,$filters = array())
	{
		benchmark_start(__METHOD__);
		$table = $this->mTable;
		$data['date_updated'] = time();
		log_message('info','Params: '.print_r($data,TRUE) ." filters: ".print_r($filters,TRUE));
		$this->db->where($filters)
				 ->update($table, $data);
		$node = $this->get_node_by_fields($filters);
		log_message('info','Updated node: '.print_r($data,TRUE));
		benchmark_end(__METHOD__);
		return $node;
	}

	/**
	 * deletes the node from the node table
	 * 
	 * @param 	(string) 	$node_id 	node_id of the node to be deleted
	 **/
	public function delete_node($node_id,$table = NULL)
	{
		benchmark_start(__METHOD__);
		if(!has_value($table)) $table = $this->mTable;
		log_message('info',"Params: {$node_id}");
		$this->db->delete($table, array('id' => $node_id));
		$this->_break_node_cache($node_id);
		benchmark_end(__METHOD__);
	}

	/**
	 * deletes the node from the node table by fields
	 * 
	 * @param 	(array) 	$fields 	table fields
	 **/
	public function delete_node_by_fields($fields,$table = NULL)
	{
		benchmark_start(__METHOD__);
		if(!has_value($table)) $table = $this->mTable;
		log_message('info',"Params: ".print_r($fields,TRUE));

		$query = $this->db->select()->from($table)->where($fields)->get();
		$results = $query->result();
		if(array_has_value($results))
		{
			//delete cache before we delete the nodes from the table
			foreach($results as $result)
			{
				$node_id = $result->id;
				$this->_break_node_cache($node_id);
			}
			$this->db->delete($table, $fields);
		}
		benchmark_end(__METHOD__);
	}

	/**
	 * queries the node from the node table based on node_id
	 * 
	 * @param 	(string) 	$node_id 	node_id of the node to be queried
	 * @return 	(array) 	node
	 **/
	public function get_node_by_id($node_id,$use_cache = TRUE)
	{
		benchmark_start(__METHOD__);
		log_message('info',"Params: {$node_id}");
        $cache = get_from_cache($this->cache_group, $node_id);
        if($cache && $use_cache === TRUE) {
        	log_message('info',__METHOD__ . ' from cache key =>'.$node_id);
        	benchmark_end(__METHOD__);
        	return $this->formatMe($cache);
        }

		$query = $this->db->get_where($this->mTable, array('id' => $node_id));
		if($query->row()) {
			put_in_cache($this->cache_group,$node_id,$query->row());
		}
		$result = $this->formatMe($query->row());
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * checks if the node exists in the node table based on node_id
	 * 
	 * @param 	(string)	$node_id 	node_id of the node to be queried
	 * @return 	(bookean)
	 **/
	public function is_node_exist($node_id)
	{
		benchmark_start(__METHOD__);
		log_message('info',"Params: {$node_id}");
		$query = $this->db->get_where($this->mTable, array('id' => $node_id));
		if($query->num_rows >= 1) $result = TRUE;
		else $result = FALSE;
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * queries the node by filtering using 2 values
	 * 
	 * @param 	(string)	$field 	field name to be filtered
	 * @param 	(mixed) 	$between_value1
	 * @return 	(array) 	array format of the node
	 **/
	public function get_nodes_between_field($field,$between_value1,$between_value2,
											$sort_field = NULL,$sort_order = 'ASC',$use_cache = TRUE)
	{
		benchmark_start(__METHOD__);
		log_message('info','Params: '.print_r($fields,TRUE));

		$database 	= $this->_database;
		$table 		= $this->mTable;

		$sql = "SELECT * 
				FROM {$mDatabase}.{$table} 
				WHERE {$field} between {$between_value1} AND {$between_value2} ";
		if(has_value($sort_field))
		{
			$sql .= " ORDER BY {$sort_field} {$sort_order}";
		}
		$result = $this->db->query($sql)->result();


		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * queries the node from the nodes table based on passed field name
	 * 
	 * @param 	(array)		$fields 	array of fields where key is the field name and the value is the value of the field
	 * @return 	(array) 	array format of the node
	 **/
	public function get_all_nodes_by_fields($fields,$sort_field = NULL,$sort_order = 'ASC',$use_cache = TRUE)
	{
		benchmark_start(__METHOD__);
		log_message('info','Params: '.print_r($fields,TRUE));
		$cache = get_from_cache($this->cache_group . 'get_all_nodes_by_fields',print_r($fields,TRUE).$sort_field.$sort_order);
        if($cache && $use_cache === TRUE) {
        	log_message('info',__METHOD__ . ' from cache');
        	benchmark_end(__METHOD__);
        	$cache = $this->format_nodes($cache);
        	return $cache;
        }

        $this->db->select()->from($this->mTable)->where($fields);
        if(has_value($sort_field))
        {
        	$this->db->order_by($sort_field, $sort_order);
    	}

    	$query = $this->db->get();
		if($query->num_rows() === 1) 
		{
			$result[] = $query->row();
		} else 
		{
			$result = $query->result();
		}
		
		if($result && !empty($fields)) put_in_cache($this->cache_group . 'get_node_by_fields',print_r($fields,TRUE),$result, 2);
		$result = $this->format_nodes($result);
		benchmark_end(__METHOD__);
		return $result;

		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * queries the node from the nodes table based on passed field name
	 * 
	 * @param 	(array)		$fields 	array of fields where key is the field name and the value is the value of the field
	 * @param 	(integer) 	$limit
	 * @param 	(integer) 	$offset
	 * @return 	(array) 	array format of the node
	 **/
	public function get_node_by_fields($fields,$limit = DEFAULT_QUERY_LIMIT,$offset = DEFAULT_QUERY_OFFSET,$sort_field = NULL,$sort_order = 'ASC',$use_cache = TRUE)
	{
		benchmark_start(__METHOD__);
		log_message('info','Params: '.print_r($fields,TRUE) . "Limit: {$limit} | Offset: {$offset}");
		$cache = get_from_cache($this->cache_group . 'get_node_by_fields',print_r($fields,TRUE).$limit.$offset.$sort_field.$sort_order);
        if($cache && $use_cache === TRUE) {
        	log_message('info',__METHOD__ . ' from cache');
        	benchmark_end(__METHOD__);
        	$cache = $this->format_nodes($cache);
        	return $cache;
        }

        $this->db->select()->from($this->mTable);

        if(array_has_value($fields))
        {
        	$this->db->where($fields);
        }
        
        if(has_value($sort_field))
        {
        	$this->db->order_by($sort_field, $sort_order);
    	}

    	if(numeric_has_value($limit) && numeric_has_value($offset))
    	{
    		$this->db->limit($limit, $offset);
    	}

    	$query =$this->db->get();
		if($query->num_rows() === 1) 
		{
			$result[] = $query->row();
		} else 
		{
			$result = $query->result();
		}
		
		if($result && !empty($fields)) put_in_cache($this->cache_group . 'get_node_by_fields',print_r($fields,TRUE).$limit.$offset,$result, 5);
		$result = $this->format_nodes($result);
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * Check if the node is existing based on given fields
	 * 
	 * @param 	(array)		$fields 	array of fields where key is the field name and the value is the value of the field
	 * @param 	(string) 	$table 		table name
	 * @return 	(boolean) 	returns TRUE if the node exists
	 **/
	public function is_node_exist_by_fields($fields,$table = NULL)
	{
		benchmark_start(__METHOD__);

		if(!has_value($table)) $table = $this->mTable;
		
		$query = $this->db->get_where($table, $fields);
		if($query->num_rows() >= 1) 
		{
			$result = TRUE;
		} else 
		{
			$result = FALSE;
		}
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * queries the node from the nodes table based on passed field name
	 * 
	 * @param 	(array) 	$fields 	array of fields where key is the field name and the value is the value of the field
	 * @param 	(integer) 	$limit
	 * @param 	(integer) 	$offset
	 * @return 	(array) 	node
	 **/
	public function get_node_like_by_fields($fields,$limit = DEFAULT_QUERY_LIMIT,$offset = DEFAULT_QUERY_OFFSET,$sort_field = NULL,$sort_order = 'asc',$use_cache = TRUE)
	{
		benchmark_start(__METHOD__);
		log_message('info','Params: '.print_r($fields,TRUE) . "Limit: {$limit} | Offset: {$offset}");
		$cache = get_from_cache($this->cache_group . 'get_node_like_by_fields',print_r($fields,TRUE).$limit.$offset.$sort_field.$sort_order);
        if($cache && $use_cache === TRUE) {
        	log_message('info',__METHOD__ . ' from cache');
        	benchmark_end(__METHOD__);
        	$cache = $this->format_nodes($cache);
        	return $cache;
        }

		$this->db->select()->from($this->mTable)->like($fields);

		if(has_value($sort_field))
        {
        	$this->db->order_by($sort_field, $sort_order);
    	}
    	$this->db->limit($limit, $offset);
		
		$query = $this->db->get();

		if($query->num_rows() === 1) 
		{
			$result[] = $query->row();
		} else 
		{
			$result = $query->result();
		}
		//$sql = $this->db->last_query();

		if($result && !empty($fields)) put_in_cache($this->cache_group . 'get_node_like_by_fields',print_r($fields,TRUE).$limit.$offset,$result, 1);
		$result = $this->format_nodes($result);
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * queries the node from the nodes table based on passed field name and using like
	 * 
	 * @param 	(array) 	$fields 	array of fields where key is the field name and the value is the value of the field
	 * @return 	(integer) 	total count of nodes
	 **/
	public function get_node_count_like_by_fields($fields = array(),$table = NULL)
	{
		benchmark_start(__METHOD__);
		if(!has_value($table)) $table = $this->mTable;
		log_message('info','Params: '.print_r($fields,TRUE));
		$query = $this->db->select('id')->from($table)->like($fields);
		$count = $query->count_all_results();
		benchmark_end(__METHOD__);
		return $count;
	}

	/**
	 * queries the node from the nodes table based on passed field name
	 * 
	 * @param 	(array) 	$fields 	array of fields where key is the field name and the value is the value of the field
	 * @return 	(integer) 	total count of nodes
	 **/
	public function get_node_count_by_fields($fields = array(),$table = NULL)
	{
		benchmark_start(__METHOD__);
		if(!has_value($table)) $table = $this->mTable;
		log_message('info','Params: '.print_r($fields,TRUE));
		$query = $this->db->select('id')->from($table)->where($fields);
		$count = $query->count_all_results();
		benchmark_end(__METHOD__);
		return $count;
	}

	/**
	 * returns the number of total nodes from the nodes table
	 * 
	 * @return 	(integer) 	total count of nodes
	 **/
	public function get_total_node_count()
	{
		benchmark_start(__METHOD__);
		$sql = "SELECT count(*) as cnt from ".$this->mDatabase.".".$this->mTable;
		$result = $this->db->query($sql);
		$result = $result->row();
		benchmark_end(__METHOD__);
		return $result->cnt;
	}

	/**
	 * returns the sum value of a field
	 * 
	 * @param 	(string) 	$field 		field to be summed up
	 * @param 	(array) 	$filters 	filters for the where clause
	 * @return 	(integer) 	sum result
	 **/
	public function get_sum($field,$filters = array())
	{
		benchmark_start(__METHOD__);
		$query = $this->db->select_sum($field)->from($this->mTable)->where($filters)->get();
		$result = $query->row();
		$sum = $result->$field;
		if($sum === NULL) $sum = 0;
		benchmark_end(__METHOD__);
		return $sum;
	}

	/**
	 * queries the node from the table based on a specific set of node_id in an array format
	 * 
	 * @param 	(array) 	$node_ids 	array of node_ids to be queried
	 * @return 	(array) 	nodes
	 **/
	public function get_nodes_where_in($field,$values)
	{
		benchmark_start(__METHOD__);
		log_message('info',"Params: {$field} ".print_r($values,TRUE));
		$cache_group = __METHOD__;
		$cached_nodes = array();
		$fresh_nodes = array();

		//check for in cache before querying nodes
		foreach($values as $key => $value)
		{
			$cache = get_from_cache($cache_group,$value);
			if(has_value($cache)) 
			{
				$cached_nodes[] = $cache;
				unset($values[$key]);
			}
		}

		//nodes that are un-cached?
		if(array_has_value($values))
		{
			$query = $this->db->select()->from($this->mTable)->where_in($field, $values)->get();
			$fresh_nodes = $query->result();
			if(!empty($fresh_nodes))
			{
				foreach($fresh_nodes as $fresh_node) {
					if(has_value($fresh_node)) put_in_cache($cache_group,$fresh_node->$field,$fresh_node);
				}
			}
		}

		//merge the cached and un-cached nodes
		$result = array_merge_recursive($cached_nodes,$fresh_nodes);
		$result = $this->format_nodes($result);
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * queries the node from the table based on a specific set of node_id in an array format
	 * 
	 * @param 	(array) 	$node_ids 	array of node_ids to be queried
	 * @return 	(array) 	nodes
	 **/
	public function get_nodes_by_id($node_ids)
	{
		benchmark_start(__METHOD__);
		log_message('info','Params: '.print_r($node_ids,TRUE));
		$cached_nodes = array();
		$fresh_nodes = array();

		//check for in cache before querying nodes
		foreach($node_ids as $key => $node_id)
		{
			$cache = get_from_cache($this->cache_group,$node_id);
			if(has_value($cache)) 
			{
				$cached_nodes[] = $cache;
				unset($node_ids[$key]);
			}
		}

		//if there are still nodes that are not cached
		if(array_has_value($node_ids))
		{
			$query = $this->db->select()->from($this->mTable)->where_in('id', $node_ids)->get();
			$fresh_nodes = $query->result();
			if(!empty($fresh_nodes))
			{
				foreach($fresh_nodes as $fresh_node) {
					if(has_value($fresh_node)) put_in_cache($this->cache_group,$fresh_node->id,$fresh_node);
				}
			}
		}

		//merge the cached and un-cached nodes
		$result = array_merge_recursive($cached_nodes,$fresh_nodes);
		$result = $this->format_nodes($result);
		benchmark_end(__METHOD__);
		return $result;
	}

	/**
	 * queries all the nodes from the nodes table in an array format
	 * 
	 * @return 	(array) 	nodes
	 **/
	public function get_all_nodes($limit = DEFAULT_QUERY_LIMIT,$offset = DEFAULT_QUERY_OFFSET,$filter = NULL,$use_cache = TRUE)
	{
		benchmark_start(__METHOD__);
		log_message('info','Params: '.print_r($filter,TRUE) . "Limit: {$limit} | Offset: {$offset}");
		$cache = get_from_cache($this->cache_group . 'get_all_nodes',print_r($filter,TRUE).$limit.$offset);
        if($cache && $use_cache === TRUE) {
        	log_message('info',__METHOD__ . ' from cache');
        	benchmark_end(__METHOD__);
        	return $cache;
		}
		
		//check if getting all the nodes is location based
		if($filter !== NULL && isset($filter['latitude'])) {
			$user_id = (isset($filter['user_id']) ? $filter['user_id'] : NULL);
			$latitude = $filter['latitude'];
			$longitude = $filter['longitude'];
			$nodes = $this->get_nodes_by_lat_lon($latitude,$longitude,$limit,$offset,$user_id);
		} else {
			if(isset($filter['sort_field']))
			{
				$sort_order = (isset($filter['sort_order']) && $filter['sort_order'] === 'desc') ? $filter['sort_order'] : 'asc';
				$sort_field = $filter['sort_field'];
				$this->db->select()->from($this->mTable)->order_by($sort_field, $sort_order);
				if($limit !== NULL && $offset !== NULL) $this->db->limit($limit, $offset);
				$query = $this->db->get();
			} else 
			{
				$query = $this->db->get($this->mTable,$limit,$offset);
			}
			$nodes = $this->format_nodes($query->result());
		}

		if($nodes) put_in_cache($this->cache_group . 'get_all_nodes',print_r($filter,TRUE).$limit.$offset,$nodes, 5);
		benchmark_end(__METHOD__);
		return $nodes;
	}

	/**
	 * asynchronously updates the user stats
	 *
	 * @param 	(string)	user_id 	id of the user
	 */
	public function update_user_stats($user_id)
	{
		$method_params = array($user_id);
		exec_background_process('user_model','update_user_stats','model',$method_params);
	}

	/**
	 * formats a single node to a standard node format
	 * 
	 * @param 	(object) 	$node 		node
	 * @return 	(array) 	node
	 **/
	public function formatMe($node)
	{
		if(!empty($node))
		{
			if(isset($node->address) && has_value($node->address))
			{
				$node->address 	= ((!empty($node->address) && !is_array($node->address)) ? ((array) json_decode($node->address)):NULL);
				$node->data 	= object_to_array($node->data);
			}
			if(isset($node->data) && has_value($node->data))
			{
				$node->data 	= ((!empty($node->data) && !is_array($node->data)) ? ((array) json_decode($node->data)):NULL);
				$node->data 	= object_to_array($node->data);
				//$node->data['delivery_no'] = (isset($node->data['delivery_no']) ? (array) json_decode($node->data['delivery_no']) : NULL);
			}
			$node = (array) $node;
			if($this->is_public === TRUE)
			{
				switch($this->mTable)
				{
					case TABLE_USERS:
						$node = filter_user_node($node);
					break;
					case TABLE_CARDS:
						$node = filter_card_node($node);
					break;
					case TABLE_MERCHANTS:
						$node = filter_merchant_node($node);
					break;
					case TABLE_DEALS:
						$node = filter_deals_node($node);
					break;
					case TABLE_USER_CATEGORIES:
					case TABLE_CATEGORIES:
						$node = filter_categories_node($node);
					case TABLE_MENUS:
					case TABLE_MENU_ITEMS:
						$node = filter_menu_node($node);
					break;
				}
			}

			return $node;
		}
		else {
			return NULL;
		}
	}

	/**
	 * formats the resultset to a standard node format
	 * 
	 * @param 	(array) 	$results 	array of node objects
	 * @return 	(array) 	nodes
	 **/
	public function format_nodes($results) {
		$result = array();
		foreach($results as $row)
		{
			$result[] = $this->formatMe($row);
		}
		return $result;
	}

	/**
	 * breakes cache
	 * 
	 * @param 	(string) 	$key 	cache key
	 **/
	private function _break_node_cache($key)
	{
		delete_from_cache($this->cache_group,$key);
		log_message('info','break cache key =>'.$key);
	}

}