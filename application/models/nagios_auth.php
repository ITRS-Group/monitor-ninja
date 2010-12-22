<?php defined('SYSPATH') OR die('No direct access allowed.');

class Nagios_auth_Model extends Model
{
	public $session = false;
	public $id = false;
	public $user = '';
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
	public $authorized_for_system_commands = false;
	public $authorized_for_all_host_commands = false;
	public $authorized_for_all_service_commands = false;
	public $authorized_for_configuration_information = false;

	public function __construct()
	{
		parent::__construct();
		$this->session = Session::instance();

		if (!Auth::instance()->logged_in()) {
			return false;
		}
		$this->user = Auth::instance()->get_user()->username;
		$this->check_rootness();

		if (empty($this->user))
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
			$contact_id = $result->count() ? $result->current()->id : false;
			Session::instance()->set('contact_id', $contact_id);
		}
		$this->id = $contact_id;
		return (int)$this->id;
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

		$query = "SELECT h.id, h.host_name FROM contact_access AS ca, host AS h WHERE ca.contact=".$this->id.
			" AND ca.service IS NULL AND h.id=ca.host";

		if ($this->view_hosts_root)
			$query = 'SELECT id, host_name from host';

		$result = $this->db->query($query);
		foreach ($result as $ary) {
			$id = $ary->id;
			$name = $ary->host_name;
			$this->hosts[$id] = $name;
			$this->hosts_r[$name] = $id;
		}

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
	*	Build host query parts for integration with other queries
	* 	that needs to know what hosts a user is authenticated to see.
	* 	These query parts doesn't assume anything like prior commas (from part)
	* 	or AND (where part) so this will have to be handled by calling method.
	*/
	public function authorized_host_query()
	{
		if ($this->view_hosts_root) {
			return true;
		}
		$query_parts = array(
			'from' => ' host AS auth_host, contact AS auth_contact, contact_contactgroup AS auth_contact_contactgroup, host_contactgroup AS auth_host_contactgroup',
			'where' => " auth_host.id = auth_host_contactgroup.host
				AND auth_host_contactgroup.contactgroup = auth_contact_contactgroup.contactgroup
				AND auth_contact_contactgroup.contact=auth_contact.id AND auth_contact.contact_name=" . $this->db->escape(Auth::instance()->get_user()->username) . "
				AND %s = auth_host.host_name",
			'host_field' => 'auth_host');
		return $query_parts;
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

		$query = "SELECT s.id, s.host_name, s.service_description FROM contact_access AS ca, service AS s WHERE ca.contact=".$this->id." AND ca.service IS NOT NULL AND s.id=ca.service";
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

		#Session::instance()->set('auth_services', $this->services);
		#Session::instance()->set('auth_services_r', $this->services_r);

		return $this->services;
	}

	/**
	*	Build service query parts for integration with other queries
	* 	that needs to know what services a user is authenticated to see.
	* 	These query parts doesn't assume anything like prior commas (from part)
	* 	or AND (where part) so this will have to be handled by calling method.
	*/
	public function authorized_service_query()
	{
		if ($this->view_services_root) {
			return true;
		}
		$query_parts = array(
			'from' => ' host AS auth_host, service AS auth_service, contact AS auth_contact, contact_contactgroup AS auth_contact_contactgroup, service_contactgroup AS auth_service_contactgroup',
			'where' => " auth_service.id = auth_service_contactgroup.service
				AND auth_service_contactgroup.contactgroup = auth_contact_contactgroup.contactgroup
				AND auth_contact_contactgroup.contact=auth_contact.id AND auth_contact.contact_name=" . $this->db->escape(Auth::instance()->get_user()->username),
			'service_field' => 'auth_service',
			'host_field' => 'auth_host',
			);
		return $query_parts;
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
		} else {
			# fetch all hostgroups with host count on each
			$query = 'SELECT hg.id, hg.hostgroup_name AS groupname, COUNT(hhg.host) AS cnt FROM '.
				'hostgroup hg, host_hostgroup hhg '.
				'WHERE hg.id=hhg.hostgroup GROUP BY hg.id';
			$result = $this->db->query($query);

			$query2 = "SELECT hg.id, hg.hostgroup_name AS groupname, COUNT(hhg.host) AS cnt FROM ".
				"hostgroup hg, host_hostgroup hhg ".
				"INNER JOIN contact_access ON contact_access.host=hhg.host ".
				"WHERE hg.id=hhg.hostgroup ".
				"AND contact_access.contact=".$this->id.
				" GROUP BY hg.id";
			$user_result = $this->db->query($query2);
			if (!count($user_result) || !count($result)) {
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
				$available_groups = false;
				$user_groups = false;
				$user_groupnames = false;
				foreach ($result as $row) {
					$available_groups[$row->id] = $row->cnt;
				}
				foreach ($user_result as $row) {
					$user_groups[$row->id] = $row->cnt;
					$user_groupnames[$row->id] = $row->groupname;
				}

				if (!empty($user_groups) && !empty($available_groups)) {
					foreach ($user_groups as $gid => $gcnt) {
						if (isset($available_groups[$gid]) && $available_groups[$gid] == $gcnt && isset($user_groupnames[$gid])) {
							$this->hostgroups[$gid] = $user_groupnames[$gid];
							$this->hostgroups_r[$user_groupnames[$gid]] = $gid;
						}
					}
				}
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

			# fetch all servicegroups with service count on each
			$query = 'SELECT sg.id, sg.servicegroup_name AS groupname, COUNT(ssg.service) AS cnt FROM '.
				'servicegroup sg, service_servicegroup ssg '.
				'WHERE sg.id=ssg.servicegroup GROUP BY sg.id';
			$result = $this->db->query($query);

			$query2 = "SELECT sg.id, sg.servicegroup_name AS groupname, COUNT(ssg.service) AS cnt FROM ".
				"servicegroup sg, service_servicegroup ssg ".
				"INNER JOIN contact_access ON contact_access.service=ssg.service ".
				"WHERE sg.id=ssg.servicegroup ".
				"AND ssg.service IS NOT NULL ".
				"AND contact_access.contact=".$this->id.
				" GROUP BY sg.id";
			$user_result = $this->db->query($query2);
			if (!count($user_result) || !count($result)) {
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
				$available_groups = false;
				$user_groups = false;
				$user_groupnames = false;
				foreach ($result as $row) {
					$available_groups[$row->id] = $row->cnt;
				}
				foreach ($user_result as $row) {
					$user_groups[$row->id] = $row->cnt;
					$user_groupnames[$row->id] = $row->groupname;
				}

				if (!empty($user_groups) && !empty($available_groups)) {
					foreach ($user_groups as $gid => $gcnt) {
						if (isset($available_groups[$gid]) && $available_groups[$gid] == $gcnt && isset($user_groupnames[$gid])) {
							$this->servicegroups[$gid] = $user_groupnames[$gid];
							$this->servicegroups_r[$user_groupnames[$gid]] = $gid;
						}
					}
				}
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
