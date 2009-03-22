<?php defined('SYSPATH') OR die('No direct access allowed.');

class Service_Model extends Model {
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	*	@name	get_service
	*	@desc	Fetch info on a specific service
	* 			by either id or name
	* 	@param  int $id
	* 	@param	str $name Should be in the form
	* 				hostname;service_description
	* 	@return object
	*
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
				$service_info = $this->db->query("
					SELECT
						s.* FROM service s,
						host h
					WHERE
						CONCAT(h.host_name, ';', s.service_description)=".$this->db->escape($name)." AND
						h.id=s.host_name");
			}
		} else {
			return false;
		}
		return $service_info !== false ? $service_info->current() : false;
	}
}
