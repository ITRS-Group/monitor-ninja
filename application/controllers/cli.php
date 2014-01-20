<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * 	CLI controller for command line access to Ninja
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
*/
class Cli_Controller extends Ninja_Controller {

	public function __construct()
	{
		# Only grant permission for cli access or if
		# user has been given the ADMIN role
		if (PHP_SAPI !== "cli" &&
			!Auth::instance()->logged_in(Ninja_Controller::ADMIN)) {
			return url::redirect('default/index');
		}
		parent::__construct();
		$this->auto_render = false;
		$op5_auth = Op5Auth::factory(array('session_key' => false));
		$op5_auth->force_user(new Op5User_AlwaysAuth());
	}

	/**
	 * Parse input data from commandline and stores in an array
	 * An equivalent to getopt() but easier for us in this environment
	 */
	private function _parse_parameters($noopt = array())
	{
		$result = array();
		$params = $GLOBALS['argv'];
		// could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
		reset($params);
		while (list($tmp, $p) = each($params)) {
			if ($p{0} == '-') {
				$pname = substr($p, 1);
				$value = true;
				if ($pname{0} == '-') {
					// long-opt (--<param>)
					$pname = substr($pname, 1);
					if (strpos($p, '=') !== false) {
						// value specified inline (--<param>=<value>)
						list($pname, $value) = explode('=', substr($p, 2), 2);
					}
				}
				// check if next parameter is a descriptor or a value
				$nextparm = current($params);
				if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-')
					list($tmp, $value) = each($params);
				$result[$pname] = $value;
			} else {
			// param doesn't belong to any option
			$result[] = $p;
			}
		}
		return $result;
	}

	private function _handle_nacoma_trigger($type, $old_name, $new_name = null) {
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}
		$this->auto_render=false;
		$cli_access = Kohana::config('config.cli_access');

		if (empty($cli_access)) {
			echo "no cli access, it's turned off in config/config.php\n";
			return false;
		}

		# figure out path from argv
		$path = $GLOBALS['argv'][0];

		// Saved reports:
		$saved_reports_model = new Saved_reports_Model();
		$report_types = array('avail', 'sla', 'summary');
		foreach ($report_types as $report_type) {
			$reports = $saved_reports_model->get_saved_reports($report_type);
			foreach ($reports as $report) {
				$report_data = $saved_reports_model->get_report_info($report_type, $report->id);
				if(!is_array(arr::search($report_data, 'objects'))) {
					continue;
				}
				if (arr::search($report_data, 'report_type') === 'services' && $type === 'host') {
					$savep = false;
					if(!is_array($report_data['objects'])) {
						continue;
					}
					foreach ($report_data['objects'] as $idx => $svc) {
						$parts = explode(';', $svc);
						if ($parts[0] === $old_name) {
							if($new_name) {
								// rename
								$report_data['objects'][$idx] = $new_name.';'.$parts[1];
							} else {
								// delete
								unset($report_data['objects'][$idx]);
							}
							$savep = true;
						}
					}
					if ($savep) {
						$saved_reports_model->save_config_objects($report_type, $report->id, $report_data['objects']);
					}
				}
				if (arr::search($report_data, 'report_type') !== ($type . 's')) {
					continue;
				}
				$key = array_search($old_name, $report_data['objects']);
				if ($key === false) {
					continue;
				}
				if($new_name) {
					// rename
					$report_data['objects'][$key] = $new_name;
				} else {
					// delete
					unset($report_data['objects'][$key]);
				}
				$saved_reports_model->save_config_objects($report_type, $report->id, $report_data['objects']);
			}
		}
	}

	/**
	 * When an object is renamed, things like scheduled reports and rrdtool data must be renamed as well
	 *
	 * @param $type string
	 * @param $old_name string
	 * @param $new_name string
	 */
	public function handle_rename($type, $old_name, $new_name)
	{
		return $this->_handle_nacoma_trigger($type, $old_name, $new_name);
	}

	/**
	 * Perform post-deletion cleanup
	 *
	 * @param $type string
	 * @param $old_name string
	 */
	public function handle_deletion($type, $old_name)
	{
		return $this->_handle_nacoma_trigger($type, $old_name);
	}

	public function save_widget()
	{
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}

		$this->auto_render=false;
		$cli_access = Kohana::config('config.cli_access');

		if (empty($cli_access)) {
			# CLI access is turned off in config/config.php
			echo "no cli access\n";
			return false;
		}

		$params = $this->_parse_parameters();
		if (!isset($params['page']) || !isset($params['name']) || !isset($params['friendly_name']))
			die("Usage: {$params[0]} {$params[1]} --page <page> --name <name> --friendly_name <friendly_name>\n");

		Ninja_widget_Model::install($params['page'], $params['name'], $params['friendly_name']);
	}

	public function rename_widget()
	{
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}
		$this->auto_render=false;
		$cli_access = Kohana::config('config.cli_access');

		if (empty($cli_access)) {
			# CLI access is turned off in config/config.php
			echo "no cli access\n";
			return false;
		}

		$params = $this->_parse_parameters();
		if (!isset($params['from']) || !isset($params['to']))
			die("Usage: {$params[0]} {$params[1]} --from <old_name> --to <new_name>\n");

		Ninja_widget_Model::rename_widget($params['from'], $params['to']);
	}

	public function rename_friendly_widget()
	{
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}
		$this->auto_render=false;
		$cli_access = Kohana::config('config.cli_access');

		if (empty($cli_access)) {
			# CLI access is turned off in config/config.php
			echo "no cli access\n";
			return false;
		}

		$params = $this->_parse_parameters();
		if (!isset($params['from']) || !isset($params['to']))
			die("Usage: {$params[0]} {$params[1]} --from <old_name> --to <new_name>\n");

		Ninja_widget_Model::rename_friendly_widget($params['from'], $params['to']);
	}

	/**
	 * Migrate avail < 10 = ninja < 2.1 = monitor < 6.0 where the meaning
	 * of host/service filter status is inverted
	 */
	public function upgrade_excluded()
	{
		ob_end_clean();
		if (PHP_SAPI !== 'cli') {
			die("illegal call\n");
		}
		$this->auto_render=false;
		$cli_access = Kohana::config('config.cli_access');

		if (empty($cli_access)) {
			# CLI access is turned off in config/config.php
			echo "no cli access\n";
			return false;
		}

		$db = Database::instance();
		$res = $db->query('SELECT version FROM avail_db_version');
		if ($res->current()->version >= 10)
			return; // already upgraded

		$reports = Saved_reports_Model::get_saved_reports('avail');
		if (!is_array($reports))
			return;

		foreach ($reports as $report) {
			$report_info = Saved_reports_Model::get_report_info('avail', $report->id);
			$report_info['host_filter_status'] = @unserialize($report_info['host_filter_status']);
			$report_info['service_filter_status'] = @unserialize($report_info['service_filter_status']);

			if (!is_array($report_info['host_filter_status'])) {
				// wooooha, duuuude
				$report_info['host_filter_status'] = array();
			} else {
				$new_filter_status = array();
				foreach (Reports_Model::$host_states as $id => $name) {
					if ($name == 'pending')
						$name = 'undetermined';
					# we need to replace the name with the id, and invert which ones areset
					if ($id == Reports_Model::HOST_EXCLUDED || isset($report_info['host_filter_status'][$name]))
						continue;
					$new_filter_status[$id] = 0;
				}
				$report_info['host_filter_status'] = $new_filter_status;
			}

			if (!is_array($report_info['service_filter_status'])) {
				$report_info['service_filter_status'] = array();
			} else {
				$new_filter_status = array();
				foreach (Reports_Model::$service_states as $id => $name) {
					# we need to replace the name with the id, and invert which ones areset
					if ($id == Reports_Model::SERVICE_EXCLUDED || isset($report_info['service_filter_status'][$name]))
						continue;
					$new_filter_status[$id] = 0;
				}
				$report_info['service_filter_status'] = $new_filter_status;
			}
			$opts = new Avail_options($report_info);

			Saved_reports_Model::edit_report_info('avail', $report->id, $opts);
		}
	}

	/**
	 * Migrate auth for ninja < 2.1 = monitor < 6.0 to op5lib backed auth
	 */
	public function upgrade_auth()
	{
		$cfg = Op5Config::instance();
		$users = $cfg->getConfig('auth_users');
		$groups = $cfg->getConfig('auth_groups');
		$db = Database::instance();
		$res = $db->query('SELECT username, realname, email, password, password_algo, system_information, configuration_information,
			system_commands, all_services, all_hosts, all_service_commands, all_host_commands
			FROM users LEFT JOIN ninja_user_authorization ON users.id = ninja_user_authorization.user_id');
		foreach ($res->result(false) as $row) {
			$username = $row['username'];
			if (isset($users[$username]))
				$user = $users[$username];
			else
				$user = array();

			foreach (array('username', 'realname', 'email', 'password', 'password_algo') as $param) {
				if (!isset($user[$param]))
					$user[$param] = $row[$param];
				unset($row[$param]);
			}
			$levels = array_filter(array_keys($row), function($arg) use ($row) {return (bool)$row[$arg];});
			if (empty($levels)) {
				// no levels, no group, no action
			} else if (count($levels) === count($row)) {
				// all levels, superuser
				$user['groups'] = array('admins');
			} else {
				if (isset($groups['user_'.$username]))
					$group = $groups['user_'.$username];
				else
					$group = array();
				$group = array_merge($group, Op5Authorization::nagios_rights_to_op5auth($levels));
				$groups['user_'.$username] = $group;
				if (!isset($user['groups']))
					$user['groups'] = array();
				$user['groups'][] = 'user_'.$username;
				$user['groups'] = array_unique($user['groups']);
			}

			$users[$username] = $user;
		}
		$now = time();
		@exec('cp /etc/op5/auth_users.yml /etc/op5/auth_users_' . $now . '.yml 2> /dev/null');
		@exec('cp /etc/op5/auth_groups.yml /etc/op5/auth_groups_' . $now . '.yml 2> /dev/null');

		$cfg->setConfig('auth_users', $users);
		$cfg->setConfig('auth_groups', $groups);
	}

	public function upgrade_recurring_downtime()
	{
		$db = Database::instance();
		$res = $db->query('SELECT * FROM recurring_downtime');
		$report = array(
			'hosts' => 'host_name',
			'services' => 'service_description',
			'hostgroups' => 'hostgroup',
			'servicegroups' => 'servicegroup'
		);
		foreach ($res->result(false) as $row) {
			if ($row['start_time'])
				continue; // already migrated
			$data = i18n::unserialize($row['data']);
			Scheduledate_Model::edit_downtime($data, $row['id']);
		}
	}

	public function license_start() {
		$row = Database::instance()->query("SELECT timestamp from report_data ORDER BY timestamp ASC LIMIT 1");
		if(!$row) {
			echo "";
			return;
		}
		$value = $row->result(false)->current();
		echo $value['timestamp'];
	}
}
