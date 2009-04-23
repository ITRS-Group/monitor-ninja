<?php defined('SYSPATH') OR die('No direct access allowed.');

class Nagios_auth_Model extends Model
{
    public $db = false;
    public $session = false;
    public $id = false;
    public $user;
    public $hosts = array();
    public $hosts_r = array();
    public $services = array();
    public $services_r = array();
    public $hostgroups = array();
    public $hostgroups_r = array();
    public $servicegroups = array();
    public $servicegroups_r = array();
    public $view_hosts_root = false;
    public $view_services_root = false;
    public $command_hosts_root = false;
    public $command_services_root = false;
    public $authorized_for_system_information = false;

    public function __construct()
    {
		parent::__construct();
		#$this->profiler = new Profiler;
		# we will always need database and session
		$this->db = new Database;
		$this->session = Session::instance();

		$this->user = Auth::instance()->get_user()->username;
		$this->check_rootness();

		if (empty($user))
			return false;

		$this->get_contact_id();
    }

    # This is required for testing purposes.
    # The backdoor side of it can safely be ignored, since the
    # reports library has zero authentication anyway, and
    # return-into-libzend or similar exploits are impossible from php
    public function i_can_has_root_plx()
    {
	    $this->view_hosts_root = true;
	    $this->view_services_root = true;
    }

    public function check_rootness()
    {
		$system = new System_Model;
		$access = $system->nagios_access($this->user);
		if (is_array($access) && !empty($access)) {
			$user_access = array_values($access);
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

		/* Allow * in cgi.cfg, which mean everybody should get 'rootness' */
		$tot_access = $system->nagios_access('*');
		if (is_array($tot_access) && !empty($tot_access)) {
			$all_access = array_values($tot_access);
			if (in_array('authorized_for_all_hosts', $all_access)) {
				$this->view_hosts_root = true;
			}

			if (in_array('authorized_for_all_services', $all_access)) {
				$this->view_services_root = true;
			}
		}
    }

    /**
     * Fetch contact id for current user
     */
    public function get_contact_id()
    {
		$query = "SELECT
				id
			FROM
				contact
			WHERE
				contact_name = ".$this->db->escape('monitor');

		$result = $this->db->query($query);
		if (!$result->current()) {
			return false;
		}
		return $result->current()->id;
    }

    /**
     * Fetch authorized hosts from db
     * for current user
     */
    public function get_authorized_hosts()
    {
		if (!empty($this->hosts))
			return $this->hosts;

		$query =
			'SELECT DISTINCT host.id, host.host_name from host, ' .
			'contact_contactgroup, contact, host_contactgroup ' .
			'WHERE host.id = host_contactgroup.host ' .
			'AND host_contactgroup.contactgroup = contact_contactgroup.contactgroup ' .
			'AND contact_contactgroup.contact = "' . $this->id.'"';

		if ($this->view_hosts_root)
			$query = 'SELECT id, host_name from host';

		$result = $this->db->query($query);
		foreach ($result as $ary) {
			$id = $ary->id;
			$name = $ary->host_name;
			$this->hosts[$id] = $name;
			$this->hosts_r[$name] = $id;
		}

		return $this->hosts;
    }

    /**
     * Fetch authorized services from db
     * for current user
     */
    public function get_authorized_services()
    {
		if (!empty($this->services))
			return $this->services;

		$query =
			'SELECT DISTINCT service.id, host.host_name, service.service_description ' .
			'FROM host, service, contact, contact_contactgroup, service_contactgroup ' .
			'WHERE service.id = service_contactgroup.service ' .
			'AND service_contactgroup.contactgroup = contact_contactgroup.contactgroup ' .
			'AND contact_contactgroup.contact = ' . $this->id . ' AND';

		if ($this->view_services_root) {
			$query = 'SELECT DISTINCT service.id, host.host_name, service.service_description ' .
			'FROM host, service WHERE';
		}

		$query .= ' host.id = service.host_name';

		$result = $this->db->query($query);
		$front = $back = array();
		foreach ($result as $ary) {
			$id = $ary->id;
			$name = $ary->host_name . ';' . $ary->service_description;
			$this->services[$id] = $name;
			$this->services_r[$name] = $id;
		}

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

		if (empty($this->hosts))
			$this->get_authorized_hosts();

		$query = 'SELECT id, hostgroup_name FROM hostgroup';
		$result = $this->db->query($query);
		foreach ($result as $ary) {
			$id = $ary->id;
			$name = $ary->hostgroup_name;
			$query = "SELECT host FROM host_hostgroup WHERE hostgroup = $id";
			$res = $this->db->query($query);
			$ok = true;
			if (!$this->view_hosts_root) {
				foreach ($res as $row) {
					if (!isset($this->hosts[$row->host])) {
						$ok = false;
						break;
					}
				}
			}

			if ($ok) {
			$this->hostgroups[$id] = $name;
			$this->hostgroups_r[$name] = $id;
			}
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

		if (empty($this->services))
			$this->get_authorized_services();

		$query = 'SELECT id, servicegroup_name FROM servicegroup';
		$result = $this->db->query($query);
		foreach ($result as $ary) {
			$id = $ary->id;
			$name = $ary->servicegroup_name;
			$query = "SELECT service FROM service_servicegroup WHERE servicegroup = $id";
			$res = $this->db->query($query);
			$ok = true;
			if (!$this->view_services_root) {
				foreach ($res as $row) {
					if (!isset($this->services[$row->service])) {
						$ok = false;
						break;
					}
				}
			}

			if ($ok) {
				$this->servicegroups[$id] = $name;
				$this->servicegroups_r[$name] = $id;
			}
		}

		return $this->servicegroups;
    }

    public function is_authorized_for_host($host)
    {
		if ($this->view_hosts_root === true)
			return true;

		if (!$this->hosts)
			$this->get_authorized_hosts();

		if (is_numeric($host)) {
			if (isset($this->hosts[$host]))
			return true;
		}
		if (isset($this->hosts_r[$host]))
			return true;

		return false;
    }

    public function is_authorized_for_service($service)
    {
		if ($this->view_services_root === true)
			return true;

		if (!$this->services)
			$this->get_authorized_services();

		if (is_numeric($service)) {
			if (isset($this->services[$service]))
			return true;
		}
		if (isset($this->services_r[$service]))
			return true;

		return false;
    }

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

    public function is_authorized_for_servicegroup($servicegroup)
    {
		if ($this->view_services_root === true)
			return true;

		if (!$this->servicegroups())
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

?>
