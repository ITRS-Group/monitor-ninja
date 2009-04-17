<?php defined('SYSPATH') OR die('No direct access allowed.');

class Host_Model extends Model {
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	*	@name 	get_host_with_services
	*	@desc	Fetch all onfo on a host. The returned object
	* 			will contain all database fields for the host object.
	* 	@param  int $id
	* 	@param	str $name
	* 	@return object
	*
	*/
	public function get_hostinfo($id=false, $name=false)
	{

		$id = (int)$id;
		$name = trim($name);

		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_info = false;

		if (!empty($id)) {
			if (!array_key_exists($id, $auth_hosts)) {
				return false;
			} else {
				$host_info = $this->db->getwhere('host', array('id' => $id));
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $this->auth->hosts_r)) {
				return false;
			} else {
				$host_info = $this->db->getwhere('host', array('host_name' => $name));
			}
		} else {
			return false;
		}
		return $host_info !== false ? $host_info->current() : false;
	}

	/**
	*	@name 	authorized_for
	*	@desc 	Determine if userr is authorized to view info
	* 			on a specific host.
	* 			Accepts either hostID or host_name as input
	* 	@param  int $id
	* 	@param	str $name
	* 	@return bool
	*
	*/
	public function authorized_for($id=false, $name=false)
	{
		$id = (int)$id;
		$name = trim($name);
		$is_auth = false;

		$auth = new Nagios_auth_Model();
		$auth_hosts = $auth->get_authorized_hosts();

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
}
