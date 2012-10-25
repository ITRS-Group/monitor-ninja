<?php defined('SYSPATH') OR die('No direct access allowed.');
require_once('auth/Auth.php');

/**
 * Model providing access to the authorization system in nagios
 *
 * Warning: a lot of these function calls are expensive! Do not create loads of instances!
 */
class Nagios_auth_Model extends Model
{
	private static $instance = false;
	public $session = false; /**< FIXME: Another user session variable, that the ninja model already provides, except we've decided not to use it */
	public $user = ''; /**< The username */
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

		if (!Auth::instance()->logged_in()) {
			return false;
		}
		$this->user = Auth::instance()->get_user();
		$this->check_rootness();

		if ($this->user === false)
			return false;
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
		if ($this->user->authorized_for('host_view_all')) {
			$this->view_hosts_root = true;
		}

		if ($this->user->authorized_for('service_view_all')) {
			$this->view_services_root = true;
		}

		if ($this->user->authorized_for('system_information')) {
			$this->authorized_for_system_information = true;
		}

		if ($this->user->authorized_for('system_commands')) {
			$this->authorized_for_system_commands = true;
		}

		if ($this->user->authorized_for('host_edit_all')) {
			$this->authorized_for_all_host_commands = true;
		}

		if ($this->user->authorized_for('service_edit_all')) {
			$this->authorized_for_all_service_commands = true;
		}

		if ($this->user->authorized_for('host_edit_all')) {
			$this->command_hosts_root = true;
		}

		if ($this->user->authorized_for('service_edit_all')) {
			$this->command_services_root = true;
		}

		if ($this->user->authorized_for('configuration_information')) {
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
	}

	/**
	 * Fetch contact id for current user
	 */
	public function get_contact_id()
	{
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
		$contact_id = Session::instance()->get('contact_id', false);
		if (empty($contact_id)) {
			$query = "SELECT id FROM contact WHERE contact_name = " .
				$this->db->escape($this->user->username);

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
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
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
		return $this->hosts;
	}

	/**
	 * Get a 'host_name' => id indexed array of authorized hosts
	 */
	public function get_authorized_hosts_r()
	{
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
		$this->get_authorized_hosts();
		return $this->hosts_r;
	}

	/**
	 * Get a "'host_name;service_description' => id"-indexed array of services
	 */
	public function get_authorized_services_r()
	{
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
		$this->get_authorized_services();
		return $this->services_r;
	}

	/**
	 * Get a "'hostgroup_name' => id"-indexed array of hostgroups
	 */
	public function get_authorized_hostgroups_r()
	{
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
		$this->get_authorized_hostgroups();
		return $this->hostgroups_r;
	}

	/**
	 * Get a "'servicegroup_name' => id"-indexed array of servicegroups
	 */
	public function get_authorized_servicegroups_r()
	{
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
		$this->get_authorized_servicegroups();
		return $this->hostgroups_r;
	}

	/**
	 * Fetch authorized services from db
	 * for current user
	 */
	public function get_authorized_services()
	{
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
		if (!empty($this->services))
			return $this->services;

		if (empty($this->id) && !$this->view_services_root && !$this->view_hosts_root)
			return array();

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
		return $this->services;
	}

	/**
	 * Fetch authorized hostgroups from db
	 * for current user
	 */
	public function get_authorized_hostgroups()
	{
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
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
throw new Exception(__CLASS__.":".__METHOD__." (".__LINE__.'): deprecated');
/* TODO: deprecate */
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
		if (is_numeric($host) && !IN_PRODUCTION)
			throw new Exception("Merlin IDs are gone, and numeric host $host - ya sure?");
		if ($this->view_hosts_root === true)
			return true;
		$out = Livestatus::instance()->getHosts(array('columns' => array('name'), 'filter' => array('name' => $host)));
		return !empty($out);
	}

	/**
	 * Return a boolean saying if we're authorized for the service name or id provided
	 *
	 * This function can be called with two arguments, where the first is host name, and the
	 * second is service description, or with one argument where host and service is ';' separated.
	 *
	 * @param $service string hostname if second arg is given, otherwize "host;service"
	 * @param $desc string = false see previous arg
	 * @return boolean
	 */
	public function is_authorized_for_service($service, $desc = false)
	{
		if (is_numeric($service) && !IN_PRODUCTION)
			throw new Exception("Merlin IDs are gone, and numeric service $service - ya sure?");
		if (!$desc) {
			if (strpos($service, ';') !== false)
				list($service, $desc) = explode(';', $service);
			else
				return false;
		}
		if ($this->view_services_root === true)
			return true;
		$out = Livestatus::instance()->getServices(array('columns' => array('description'), 'filter' => array('host_name' => $service, 'descritpion' => $desc)));
		return !empty($out);
	}

	/**
	 * @param $hostgroup string
	 * @return boolean
	 */
	public function is_authorized_for_hostgroup($hostgroup)
	{
		if (is_numeric($hostgroup) && !IN_PRODUCTION)
			throw new Exception("Merlin IDs are gone, and numeric hostgroup $hostgroup - ya sure?");
		if ($this->view_hosts_root === true)
			return true;
		$out = Livestatus::instance()->getHostgroups(array('columns' => array('name'), 'filter' => array('name' => $hostgroup)));
		return !empty($out);
	}

	/**
	 * @param $servicegroup string
	 * @return boolean
	 */
	public function is_authorized_for_servicegroup($servicegroup)
	{
		if (is_numeric($servicegroup) && !IN_PRODUCTION)
			throw new Exception("Merlin IDs are gone, and numeric servicegroup $servicegroup - ya sure?");
		if ($this->view_services_root === true)
			return true;
		$out = Livestatus::instance()->getHostgroups(array('columns' => array('name'), 'filter' => array('name' => $servicegroup)));
		return !empty($out);
	}
}
