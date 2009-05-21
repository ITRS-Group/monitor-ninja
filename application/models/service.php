<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate service status data
 */
class Service_Model extends Model
{
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	 * Fetch info on a specific service by either id or name
	 * @param $id Id of the service
	 * @param $name Name of the service. This must be in the form
	 *              hostname;service_description
	 * @return Service object on success, false on errors
	 */
	public function get_serviceinfo($id=false, $name=false)
	{
		$id = (int)$id;
		$name = trim($name);

		$auth_services = $this->auth->get_authorized_services();
		$service_info = false;

		if (!empty($id)) {
			if (!array_key_exists($id, $auth_services)) {
				return false;
			} else {
				$service_info = $this->db->getwhere('service', array('id' => $id));
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $this->auth->services_r)) {
				return false;
			} else {
				$service_info = $this->db->query
					("SELECT  * FROM service s" .
					 "WHERE CONCAT(s.host_name, ';', s.service_description)=".
					 $this->db->escape($name));
			}
		}

		return $service_info !== false ? $service_info->current() : false;
	}

	/**
	*	Fetch services that belongs to a specific service- or hostgroup
	*/
	public function get_services_for_group($group=false, $type='service')
	{
		$type = trim($type);
		if (empty($group) || empty($type)) {
			return false;
		}
		$auth_services = $this->auth->get_authorized_services();
		$service_str = implode(', ', array_keys($auth_services));
		switch ($type) {
			case 'service':
				$sql = "SELECT
					s.*
				FROM
					service s,
					servicegroup sg,
					service_servicegroup ssg
				WHERE
					sg.servicegroup_name=".$this->db->escape($group)." AND
					ssg.servicegroup = sg.id AND
					s.id=ssg.service AND
					s.id IN(".$service_str.")
				ORDER BY
					s.service_description";
					break;
			case 'host':
				$sql = "SELECT
					s.*
				FROM
					service s,
					host h,
					hostgroup hg,
					host_hostgroup hhg
				WHERE
					hg.hostgroup_name=".$this->db->escape($group)." AND
					hhg.hostgroup = hg.id AND
					s.host_name=h.host_name AND
					hhg.host = h.id AND
					s.id IN(".$service_str.")
				ORDER BY
					s.service_description";
				break;
		}
		if (!empty($sql)) {
			$result = $this->db->query($sql);
			return $result;
		}
		return false;
	}

	/**
	*	Fetch services that belongs to a specific service- or hostgroup
	*/
	public function get_hosts_for_group($group=false, $type='servicegroup')
	{
		$type = trim($type);
		if (empty($group) || empty($type)) {
			return false;
		}
		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_str = implode(', ', array_keys($auth_hosts));
		switch ($type) {
			case 'servicegroup':
				$sql = "SELECT
					DISTINCT h.*
				FROM
					service s,
					host h,
					servicegroup sg,
					service_servicegroup ssg
				WHERE
					sg.servicegroup_name=".$this->db->escape($group)." AND
					ssg.servicegroup = sg.id AND
					s.id=ssg.service AND
					h.host_name=s.host_name AND
					h.id IN(".$host_str.")
				ORDER BY
					h.host_name";
				case 'hostgroup':
				break;
		}
		if (!empty($sql)) {
			$result = $this->db->query($sql);
			return $result;
		}
		return false;
	}

	/**
	*
	*	Fetch service info filtered on specific field and value
	*/
	public function get_where($field=false, $value=false, $limit=false)
	{
		if (empty($field) || empty($value)) {
			return false;
		}
		$auth_objects = $this->auth->get_authorized_services();
		$obj_ids = array_keys($auth_objects);
		$obj_info = $this->db
			->from('service')
			->like($field, $value)
			->in('id', $obj_ids)
			->limit($limit)
			->get();
		return $obj_info;
	}

	/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false)
	{
		if (empty($value)) return false;
		$auth_obj = $this->auth->get_authorized_services();
		$obj_ids = array_keys($auth_obj);
		$obj_info = $this->db
			->select('DISTINCT s.*, h.current_state AS host_state')
			->from('service AS s, host AS h')
			->orlike(
				array(
					's.host_name' => $value,
					's.service_description' => $value,
					's.display_name' => $value
					)
				)
			->where('s.host_name=h.host_name')
			->in('s.id', $obj_ids)
			->groupby('s.id')
			->limit($limit)
			->get();
		return $obj_info;
	}
}
