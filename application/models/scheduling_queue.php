<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle comments for hosts and services
 */
class Scheduling_queue_Model extends Model {

	public $sort_field ='next_check'; /**< The field to sort the results on */
	public $sort_order='ASC'; /**< The sort order, 'ASC' or 'DESC' */
	private $host_qry = false;
	private $svc_qry = false;

	/**
	 * Fetch scheduled events
	 *
	 * @param $num_per_page The number of results per page
	 * @param $offset The number of result rows to skip
	 * @param $count Don't return any result rows, only the total number of results
	 * @return Database result object or false if none if $count is false or unset, otherwise the number of result rows
	 */
	public function show_scheduling_queue($num_per_page=false, $offset=false, $count=false)
	{

		$db = Database::instance();
		$auth = Nagios_auth_Model::instance();

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
							") UNION ALL (".
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
	 * Set class variable host_qry to use when searching
	 *
	 * @param $qry A search string to apply against host names
	 */
	public function set_host_search_term($qry=false)
	{
		if (!empty($qry)) {
			$this->host_qry = '%'.$qry.'%';
		}
	}
	/**
	 * Set class variable svc_qry to use when searching
	 *
	 * @param $qry A search string to apply against service descriptions
	 */
	public function set_service_search_term($qry=false)
	{
		if (!empty($qry)) {
			$this->svc_qry = '%'.$qry.'%';
		}
	}

}
