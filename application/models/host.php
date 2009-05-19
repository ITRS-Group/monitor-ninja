<?php defined('SYSPATH') OR die('No direct access allowed.');

class Host_Model extends Model {
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	 * Fetch all onfo on a host. The returned object
	 * will contain all database fields for the host object.
	 * @param $name The host_name of the host
	 * @param $id The id of the host
	 * @return Host object on success, false on errors
	 */
	public function get_hostinfo($name=false, $id=false)
	{

		$id = (int)$id;
		$name = trim($name);

		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_info = false;

		if (!empty($id)) {
			if (!array_key_exists($id, $auth_hosts)) {
				return false;
			} else {
				$host_info = $this->db
					->select('*, (UNIX_TIMESTAMP() - last_state_change) AS duration, output AS plugin_output')
					->where('host', array('id' => $id));
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $this->auth->hosts_r)) {
				return false;
			} else {
				$host_info = $this->db
					->select('*, (UNIX_TIMESTAMP() - last_state_change) AS duration, output AS plugin_output')
					->getwhere('host', array('host_name' => $name));
			}
		} else {
			return false;
		}
		return $host_info !== false ? $host_info->current() : false;
	}

	/**
	 * Determine if user is authorized to view info on a specific host.
	 * Accepts either hostID or host_name as input
	 *
	 * @param $name The host_name of the host.
	 * @param $id The id of the host
	 * @return True if authorized, false if not.
	 */
	public function authorized_for($name=false, $id=false)
	{
		$id = (int)$id;
		$name = trim($name);
		$is_auth = false;

		$auth_hosts = $this->auth->get_authorized_hosts();

		if (!empty($id)) {
			if (!array_key_exists($id, $auth_hosts)) {
				return false;
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $auth->hosts_r)) {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Fetch breakdown of current service states for a host
	 * @param $host_name The host_name of the host
	 * @param $host_id The id of the host object
	 * @return false on errors, array of services on success
	 */
	public function service_states($host_name=false, $host_id=false)
	{
		$s = !empty($host_id) ? '' : 's.';
		$auth_query_parts = $this->auth->authorized_service_query();
		$auth_from = '';
		$auth_where = '';
		if ($auth_query_parts !== true) {
			$auth_from = ', '.$auth_query_parts['from'];

			# match authorized services against service.host_name
			$auth_where = ' AND '.sprintf($auth_query_parts['where'], $s.'host_name');
		}
		if (!empty($host_id)) {
			$sql = "
				SELECT
					COUNT(current_state) AS cnt,
					current_state
				FROM
					service ".$auth_from."
				WHERE
					host_name=".(int)$host_id." ".$auth_where."
				GROUP BY
					current_state;";
		} else {
			$sql = "
				SELECT
					COUNT(s.current_state) AS cnt,
					s.current_state
				FROM
					service s,
					host h ".$auth_from."
				WHERE
					h.host_name=".$this->db->escape($host_name)." AND
					s.host_name=h.host_name ".$auth_where."
				GROUP BY
					s.current_state;";
		}
		$res = $this->db->query($sql);
		return $res;
	}

	/**
	*
	*	Fetch host info filtered on specific field and value
	*/
	public function get_where($field=false, $value=false, $limit=false)
	{
		if (empty($field) || empty($value)) {
			return false;
		}
		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_ids = array_keys($auth_hosts);
		$host_info = $this->db
			->from('host')
			->like($field, $value)
			->in('id', $host_ids)
			->limit($limit)
			->get();
		return $host_info;
	}
}
