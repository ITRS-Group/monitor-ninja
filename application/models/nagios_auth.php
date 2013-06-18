<?php defined('SYSPATH') OR die('No direct access allowed.');

//var_dump(get_include_path());
//var_dump(glob('/usr/share/php/*'));
require_once('op5/auth/Auth.php');

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
	private $view_services_root = false; /**< Is user authorized to see all services? */
	private $command_hosts_root = false; /**< Is user authorized to issue all host commands? WARNING: we ignore this way too much */
	private $command_services_root = false; /**< Is user authorized to issue all servicecommands? WARNING: we ignore this way too much */
	private $authorized_for_system_information = false; /**< Is the user authorized to see system information? WARNING: we ignore this way too much */
	private $authorized_for_system_commands = false; /**< Is the user authorized to issue system-wide commands? WARNING: we ignore this way too much*/
	private $authorized_for_all_host_commands = false; /**< Alias for command_hosts_root */
	private $authorized_for_all_service_commands = false; /**< Alias for command_services_root */
	private $authorized_for_configuration_information = false; /**< Is the user authorized to see information about the global configuration? */

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
			return;
		}
		$this->user = Auth::instance()->get_user();
		$this->check_rootness();
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
		$out = Livestatus::instance()->getServices(array('columns' => array('description'), 'filter' => array('host_name' => $service, 'description' => $desc)));
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
