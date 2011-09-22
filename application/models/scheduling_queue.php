<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle comments for hosts and services
 */
class Scheduling_queue_Model extends Model {

	public $sort_field ='next_check';
	public $sort_order='ASC';
	public $num_per_page = false;
	public $offset = false;
	public $count = false;
	public $host_qry = false;
	public $svc_qry = false;

	/**
	*	Fetch scheduled events
	*
	*/
	public function show_scheduling_queue($num_per_page=false, $offset=false, $count=false)
	{

		$db = Database::instance();
		$auth = new Nagios_auth_Model();

		if ($auth->view_hosts_root) {

			$num_per_page = (int)$num_per_page;
			$search_sql_host = false;
			$search_sql_svc = false;
			$prevent_host_query = false;
			if (!empty($this->host_qry)) {
				$search_sql_host = ' AND LCASE(host_name) LIKE LCASE('.$this->db->escape($this->host_qry).') ';
			}

			if (!empty($this->svc_qry)) {
				$search_sql_svc = ' AND LCASE(service_description) LIKE LCASE('.$this->db->escape($this->svc_qry).') ';
				$prevent_host_query = ' AND 2=1';
			}

			# only use LIMIT when NOT counting
			if ($offset !== false)
				$offset_limit = $count!==false ? "" : " LIMIT " . $num_per_page." OFFSET ".$offset;
			else
				$offset_limit = '';

			$sql = "(SELECT host_name, service_description, next_check, last_check, check_type, active_checks_enabled ".
							"FROM service ".
							"WHERE should_be_scheduled=1".$search_sql_svc.$search_sql_host.
							") UNION (".
							"SELECT host_name, CONCAT('', '') as service_description, next_check, last_check, check_type, active_checks_enabled ".
							"FROM host ".
							"WHERE should_be_scheduled=1".$search_sql_host.$prevent_host_query.
							") ORDER BY ".$this->sort_field." ".$this->sort_order." ".$offset_limit;

			$result = $db->query($sql);

			if ($count !== false) {
				return $result ? count($result) : 0;
			}
			return $result->count() ? $result->result(): false;
		}
	}

	/**
	*	Wrapper method to fetch no of hosts in the scheduling queue
	*/
	public function count_queue()
	{
		return self::show_scheduling_queue(false, false, true);
	}

	/**
	*	Set class variable host_qry to use when searching
	*
	*/
	public function set_host_search_term($qry=false)
	{
		if (!empty($qry)) {
			$this->host_qry = '%'.$qry.'%';
		}
	}
	/**
	*	Set class variable svc_qry to use when searching
	*
	*/
	public function set_service_search_term($qry=false)
	{
		if (!empty($qry)) {
			$this->svc_qry = '%'.$qry.'%';
		}
	}

}
