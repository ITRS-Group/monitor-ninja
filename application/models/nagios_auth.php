<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Model providing access to the authorization system in nagios
 *
 * Warning: a lot of these function calls are expensive! Do not create loads of instances!
 */
class Nagios_auth_Model extends Model
{
	private static $instance = false;
	public $session = false; /**< FIXME: Another user session variable, that the ninja model already provides, except we've decided not to use it */
	public $id = false; /**< The user id */
	public $user = ''; /**< The username */
	public $hosts = array(); /**< An id->host_name map of hosts the user is authorized to see */
	public $hosts_r = array(); /**< An host_name->id map of hosts the user is authorized to see */
	public $services = array(); /**< An id->service map of servicesthe user is authorized to see */
	public $services_r = array(); /**< An service->id map of servicesthe user is authorized to see */
	public $hostgroups = array(); /**< An id->hostgroup_name map of hostgroups the user is authorized to see */
	public $hostgroups_r = array(); /**< An hostgroup_name->id map of hostgroups the user is authorized to see */
	public $servicegroups = array(); /**< An id->servicegroup_name map of servicegroups the user is authorized to see */
	public $servicegroups_r = array(); /**< An servicegroup_name->id map of servicegroups the user is authorized to see */
	public $view_hosts_root = false; /**< Is user authorized to see all hosts? */
	public $view_services_root = false; /**< Is user authorized to see all services? */
	public $command_hosts_root = false; /**< Is user authorized to issue all host commands? WARNING: we ignore this way too much */
	public $command_services_root = false; /**< Is user authorized to issue all servicecommands? WARNING: we ignore this way too much */
	public $authorized_for_system_information = false; /**< Is the user authorized to see system information? WARNING: we ignore this way too much */
	public $authorized_for_system_commands = false; /**< Is the user authorized to issue system-wide commands? WARNING: we ignore this way too much*/
	public $authorized_for_all_host_commands = false; /**< Alias for command_hosts_root */
	public $authorized_for_all_service_commands = false; /**< Alias for command_services_root */
	public $authorized_for_configuration_information = false; /**< Is the user authorized to see information about the global configuration? */

	/**
	 * Return the singleton instance of the auth model
	 */
	public static function instance() {
		if (!self::$instance)
			self::$instance = new Nagios_auth_Model();
		return self::$instance;
	}

	/**
	 * Almost anything you can do with this model is expensive and cached.
	 * Thus, do /NOT/ call this constructor directly - use self::instance()
	 * instead.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->session = Session::instance();

		if (!Auth_Core::instance()->logged_in()) {
			return false;
		}
		$this->user = Auth::instance()->get_user()->username;
		$this->check_rootness();

		if (empty($this->user))
			return false;

		$this->get_contact_id();
	}

	/**
	 * This is required for testing purposes.
	 * The backdoor side of it can safely be ignored, since the
	 * reports library has zero authentication anyway, and
	 * return-into-libzend or similar exploits are impossible from php
	 */
	public function i_can_has_root_plx()
	{
		$this->view_hosts_root = true;
		$this->view_services_root = true;
	}

	/**
	 * Initializes the user authorization levels.
	 */
	public function check_rootness()
	{
		$access = System_Model::nagios_access($this->user);
		if (empty($access))
			return;

		if (is_array($access) && !empty($access)) {
			$user_access = array_keys($access);
		}

		if (in_array('authorized_for_all_hosts', $user_access)) {
			$this->view_hosts_root = true;
		}

		if (in_array('authorized_for_all_services', $user_access)) {
			$this->view_services_root = true;
		}

		if (in_array('authorized_for_system_information', $user_access)) {
			$this->authorized_for_system_information = true;
		}

		if (in_array('authorized_for_system_commands', $user_access)) {
			$this->authorized_for_system_commands = true;
		}

		if (in_array('authorized_for_all_host_commands', $user_access)) {
			$this->authorized_for_all_host_commands = true;
		}

		if (in_array('authorized_for_all_service_commands', $user_access)) {
			$this->authorized_for_all_service_commands = true;
		}

		if (in_array('authorized_for_all_host_commands', $user_access)) {
			$this->command_hosts_root = true;
		}

		if (in_array('authorized_for_all_service_commands', $user_access)) {
			$this->command_services_root = true;
		}

		if (in_array('authorized_for_configuration_information', $user_access)) {
			$this->authorized_for_configuration_information = true;
		}

		if ($this->authorized_for_all_host_commands && $this->view_hosts_root) {
			$this->command_hosts_root = true;
		}

		# according to http://nagios.sourceforge.net/docs/3_0/configcgi.html
		# regarding authorized_for_all_host_commands
		# "Users in this list are also automatically authorized to
		#  issue commands for all services."
		if ($this->command_hosts_root) {
			$this->command_services_root = true;
		}

		/* Allow * in cgi.cfg, which mean everybody should get 'rootness' */
		/*
		#@@@FIXME: We should handle this when importing data from cgi.cfg
		$tot_access = System_Model::nagios_access('*');
		if (is_array($tot_access) && !empty($tot_access)) {
			$all_access = array_values($tot_access);
			if (in_array('authorized_for_all_hosts', $all_access)) {
				$this->view_hosts_root = true;
			}

			if (in_array('authorized_for_all_services', $all_access)) {
				$this->view_services_root = true;
			}
		}
		*/
	}

	/**
	 * Fetch contact id for current user
	 */
	public function get_contact_id()
	{
		$contact_id = Session::instance()->get('contact_id', false);
		if (empty($contact_id)) {
			$query = "SELECT id FROM contact WHERE contact_name = " .
				$this->db->escape($this->user);

			$result = $this->db->query($query);
			$contact_id = $result->count() ? $result->current()->id : -1;
			unset($result);
			Session::instance()->set('contact_id', $contact_id);
		}
		$this->id = (int)$contact_id;
		return $this->id;
	}

	/**
	 * Fetch authorized hosts from db
	 * for current user
	 */
	public function get_authorized_hosts()
	{
		#$this->hosts = Session::instance()->get('auth_hosts', false);
		#$this->hosts_r = Session::instance()->get('auth_hosts_r', false);

		if (!empty($this->hosts))
			return $this->hosts;

		if (empty($this->id) && !$this->view_hosts_root)
			return array();

		$query = "SELECT h.id, h.host_name FROM contact_access ca INNER JOIN host h ON h.id=ca.host WHERE ca.contact=".$this->id;

		if ($this->view_hosts_root)
			$query = 'SELECT id, host_name from host';

		$result = $this->db->query($query);
		foreach ($result as $ary) {
			$id = $ary->id;
			$name = $ary->host_name;
			$this->hosts[$id] = $name;
			$this->hosts_r[$name] = $id;
		}
		unset($result);
		#Session::instance()->set('auth_hosts', $this->hosts);
		#Session::instance()->set('auth_hosts_r', $this->hosts_r);

		return $this->hosts;
	}

	/**
	 * Get a 'host_name' => id indexed array of authorized hosts
	 */
	public function get_authorized_hosts_r()
	{
		$this->get_authorized_hosts();
		return $this->hosts_r;
	}

	/**
	 * Get a "'host_name;service_description' => id"-indexed array of services
	 */
	public function get_authorized_services_r()
	{
		$this->get_authorized_services();
		return $this->services_r;
	}

	/**
	 * Get a "'hostgroup_name' => id"-indexed array of hostgroups
	 */
	public function get_authorized_hostgroups_r()
	{
		$this->get_authorized_hostgroups();
		return $this->hostgroups_r;
	}

	/**
	 * Get a "'servicegroup_name' => id"-indexed array of servicegroups
	 */
	public function get_authorized_servicegroups_r()
	{
		$this->get_authorized_servicegroups();
		return $this->hostgroups_r;
	}

	/**
	 * Fetch authorized services from db
	 * for current user
	 */
	public function get_authorized_services()
	{
		if (!empty($this->services))
			return $this->services;

		if (empty($this->id) && !$this->view_services_root && !$this->view_hosts_root)
			return array();

		#$this->services = Session::instance()->get('auth_services', false);
		#$this->services_r = Session::instance()->get('auth_services_r', false);
		if (!empty($this->services))
			return $this->services;

		$query = "SELECT s.id, s.host_name, s.service_description FROM contact_access ca, service s WHERE ca.contact=".$this->id." AND ca.service IS NOT NULL AND s.id=ca.service";
		if ($this->view_services_root || $this->view_hosts_root) {
			$query = 'SELECT id, host_name, service_description FROM service';
		}
		$result = $this->db->query($query);
		$front = $back = array();
		foreach ($result as $ary) {
			$id = $ary->id;
			$name = $ary->host_name . ';' . $ary->service_description;
			$this->services[$id] = $name;
			$this->services_r[$name] = $id;
		}
                unset($result);
		#Session::instance()->set('auth_services', $this->services);
		#Session::instance()->set('auth_services_r', $this->services_r);

		return $this->services;
	}

	/**
	 * Fetch authorized hostgroups from db
	 * for current user
	 */
	public function get_authorized_hostgroups()
	{
		if (!empty($this->hostgroups))
			return $this->hostgroups;

		if ($this->view_hosts_root) {
			$query = 'SELECT id, hostgroup_name FROM hostgroup';
			$result = $this->db->query($query);
			if (count($result)) {
				foreach ($result as $row) {
					$this->hostgroups[$row->id] = $row->hostgroup_name;
					$this->hostgroups_r[$row->hostgroup_name] = $row->id;
				}
			}
                        unset($result);
		} else {
			if (empty($this->id))
				return false;

			$query2 = "SELECT hg.id, hg.hostgroup_name AS groupname, COUNT(hhg.host) AS cnt FROM ".
				"hostgroup hg INNER JOIN host_hostgroup hhg ON hg.id=hhg.hostgroup ".
				"INNER JOIN contact_access ON contact_access.host=hhg.host ".
				"WHERE contact_access.contact=".$this->id.
				" GROUP BY hg.id, hg.hostgroup_name";
			$user_result = $this->db->query($query2);
			if (!count($user_result)) {
				unset($user_result);
				return false;
			}
			$see_all_hostgroups = Kohana::config('groups.see_partial_hostgroups');

			if ($see_all_hostgroups !== false) {
				foreach ($user_result as $ary) {
					if ($ary->cnt !=0) {
						$this->hostgroups[$ary->id] = $ary->groupname;
						$this->hostgroups_r[$ary->groupname] = $ary->id;
					}
				}
			} else {
				# fetch all hostgroups with host count on each
				$query = 'SELECT hg.id, hg.hostgroup_name AS groupname, COUNT(hhg.host) AS cnt FROM '.
					'hostgroup hg, host_hostgroup hhg '.
					'WHERE hg.id=hhg.hostgroup GROUP BY hg.id, hg.hostgroup_name';
				$result1 = $this->db->query($query);
				$result = array();
				foreach( $result1 as $row) {
					$result[] = $row;
				}
				if (!count($result))
					return false;

				$available_groups = false;
				$user_groups = false;
				$user_groupnames = false;
				foreach ($result as $row) {
					$available_groups[$row->id] = $row->cnt;
				}
				foreach ($user_result as $row) {
					if (isset($available_groups[$row->id]) && $row->cnt && $row->cnt === $available_groups[$row->id]) {
						$this->hostgroups[$row->id] = $row->groupname;
						$this->hostgroups_r[$row->groupname] = $row->id;
					}
				}
			}
			unset($user_result);
		}

		return $this->hostgroups;
	}

	/**
	 * Fetch authorized servicegroups from db
	 * for current user
	 */
	public function get_authorized_servicegroups()
	{
		if (!empty($this->servicegroups))
			return $this->servicegroups;

		if ($this->view_hosts_root || $this->view_services_root) {
			$query = 'SELECT id, servicegroup_name FROM servicegroup';
			$result = $this->db->query($query);
			if (count($result)) {
				foreach ($result as $row) {
					$this->servicegroups[$row->id] = $row->servicegroup_name;
					$this->servicegroups_r[$row->servicegroup_name] = $row->id;
				}
			}
		} else {

			if (empty($this->id))
				return false;

			$query2 = "SELECT sg.id, sg.servicegroup_name AS groupname, COUNT(ssg.service) AS cnt FROM ".
				"servicegroup sg INNER JOIN service_servicegroup ssg ON sg.id = ssg.servicegroup ".
				"INNER JOIN contact_access ON contact_access.service=ssg.service ".
				"WHERE contact_access.contact=".$this->id.
				" GROUP BY sg.id, sg.servicegroup_name";
			$user_result = $this->db->query($query2);
			if (!count($user_result)) {
				unset($user_result);
				return false;
			}
			$see_all_servicegroups = Kohana::config('groups.see_partial_servicegroups');

			if ($see_all_servicegroups !== false) {
				foreach ($user_result as $ary) {
					if ($ary->cnt !=0) {
						$this->servicegroups[$ary->id] = $ary->groupname;
						$this->servicegroups_r[$ary->groupname] = $ary->id;
					}
				}
			} else {
				# fetch all servicegroups with service count on each
				$query = 'SELECT sg.id, sg.servicegroup_name AS groupname, COUNT(ssg.service) AS cnt FROM '.
					'servicegroup sg, service_servicegroup ssg '.
					'WHERE sg.id=ssg.servicegroup GROUP BY sg.id, sg.servicegroup_name';
				$result = $this->db->query($query);
				if (!count($result)) {
					return false;
				}

				$available_groups = false;
				foreach ($result as $row) {
					$available_groups[$row->id] = $row->cnt;
				}
				$user_groups = false;
				$user_groupnames = false;

				foreach ($user_result as $row) {
					if (isset($available_groups[$row->id]) && $row->cnt && $row->cnt === $available_groups[$row->id]) {
						$this->servicegroups[$row->id] = $row->groupname;
						$this->servicegroups_r[$row->groupname] = $row->id;
					}
				}
			}
		}
		unset($user_result);

		return $this->servicegroups;
	}

	/**
	 * @param $host string
	 * @return boolean
	 */
	public function is_authorized_for_host($host)
	{
		if ($this->view_hosts_root === true)
			return true;

		// should always return "0" or "1"
		if (is_numeric($host))
			$query = 'SELECT count(1) AS cnt FROM contact_access WHERE host = '.$host.' AND contact = '.$this->id;
		else
			$query = 'SELECT count(1) AS cnt FROM contact_access ca INNER JOIN host ON host.id = ca.host WHERE host_name = '.$this->db->escape($host).' AND contact = '.$this->id;
		$res = $this->db->query($query);
		return ($res->current()->cnt != '0');
	}

	/**
	 * Return a boolean saying if we're authorized for the service name or id provided
	 *
	 * This function can be called with one numeric argument, in which case
	 * it's assumed to be a service ID - the resulting query is quick.
	 *
	 * It can be called with two arguments, where the first is host name, and the
	 * second is service description. This is quite quick, but not quite as quick
	 * as the first option.
	 *
	 * Or it can be called with a string containing ';' representing the
	 * complete host_name/service_description in one argument, which is
	 * slightly slower than both the other two.
	 *
	 * @param $service string hostname if second arg is given, otherwize "host;service"
	 * @param $desc string = false see previous arg
	 * @return boolean
	 */
	public function is_authorized_for_service($service, $desc = false)
	{
		if ($this->view_services_root === true)
			return true;

		/*
		 * we must check if $desc is false here so we properly
		 * handle hosts named '1', '2' etc.
		 */
		if ((is_int($service) || is_numeric($service)) && $desc === false) {
			$query = 'SELECT count(1) AS cnt FROM contact_access WHERE host = '.$service.' AND contact = '.$this->id;
		} else {
			if ($desc === false) {
				if (strpos($service, ';') < 1)
					return false; /* bogus input */

				$ary = explode(';', $service, 2);
				$desc = $ary[1];
				$service = $ary[0];
			}

			$query = 'SELECT count(1) AS cnt FROM contact_access ca INNER JOIN service ON service.id = ca.service WHERE service.host_name = '.$this->db->escape($service).' AND service.service_description = '.$this->db->escape($desc).' AND contact = '.$this->id;
		}

		$res = $this->db->query($query);
		return ($res->current()->cnt != '0');
	}

	/**
	 * @param $hostgroup string
	 * @return boolean
	 */
	public function is_authorized_for_hostgroup($hostgroup)
	{
		if ($this->view_hosts_root === true)
			return true;

		if (!$this->hostgroups)
			$this->get_authorized_hostgroups();

		if (is_numeric($hostgroup)) {
			if (isset($this->hostgroups[$hostgroup]))
			return true;
		}
		if (isset($this->hostgroups_r[$hostgroup]))
			return true;

		return false;
	}

	/**
	 * @param $servicegroup string
	 * @return boolean
	 */
	public function is_authorized_for_servicegroup($servicegroup)
	{
		if ($this->view_services_root === true)
			return true;

		if (!$this->servicegroups)
			$this->get_authorized_servicegroups();

		if (is_numeric($servicegroup)) {
			if (isset($this->servicegroups[$servicegroup]))
			return true;
		}
		if (isset($this->servicegroups_r[$servicegroup]))
			return true;

		return false;
	}
}
