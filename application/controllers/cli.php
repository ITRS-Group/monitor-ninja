<?php defined('SYSPATH') OR die('No direct access allowed.');
require_once('op5/auth/Auth.php');
require_once('op5/auth/User_AlwaysAuth.php');
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
class Cli_Controller extends Controller {

	public function __construct()
	{
		if (PHP_SAPI !== "cli") {
			url::redirect('default/index');
			return;
		}
		parent::__construct();
		$op5_auth = Op5Auth::instance();
		$op5_auth->write_close();
		$op5_auth->force_user(new Op5User_AlwaysAuth());
	}

	/**
	 * Parse input data from commandline and stores in an array, equivalent
	 * to getopt().
	 */
	private function _parse_parameters($noopt = array())
	{
		$result = array();
		$params = $GLOBALS['argv'];
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

		$report_types = array('avail', 'sla', 'summary', 'histogram');
		foreach ($report_types as $report_type) {
			$obj = Report_options::setup_options_obj($report_type);
			$reports = $obj->get_all_saved();
			foreach ($reports as $report_id => $_) {
				$report_data = Report_options::setup_options_obj($report_type, array('report_id' => $report_id));
				// The report might use the magical "All" string, instead of an array, in which case we're done
				if (!is_array($report_data['objects']))
					continue;
				if ($report_data['report_type'] === 'services' && $type === 'host') {
					$savep = false;
					foreach ($report_data['objects'] as $idx => $name) {
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
					}
					if ($savep) {
						$report_data->save();
					}
				}
				if ($report_data['report_type'] != $type . 's') {
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
				$report_data->save();
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

		$reports = $db->query('SELECT id, host_filter_status, service_filter_status FROM avail_config');
		foreach ($reports as $report) {
			$host_filter_status = @unserialize($report->host_filter_status);
			$service_filter_status = @unserialize($report->service_filter_status);

			if (!is_array($host_filter_status)) {
				$host_filter_status = array();
			} else {
				$new_filter_status = array();
				foreach (Reports_Model::$host_states as $id => $name) {
					if ($name == 'pending')
						$name = 'undetermined';
					# we need to replace the name with the id, and invert which ones are set
					if ($id == Reports_Model::HOST_EXCLUDED || isset($host_filter_status[$name]))
						continue;
					$new_filter_status[$id] = 0;
				}
				$host_filter_status = $new_filter_status;
			}

			if (!is_array($service_filter_status)) {
				$service_filter_status = array();
			} else {
				$new_filter_status = array();
				foreach (Reports_Model::$service_states as $id => $name) {
					# we need to replace the name with the id, and invert which ones are set
					if ($id == Reports_Model::SERVICE_EXCLUDED || isset($service_filter_status[$name]))
						continue;
					$new_filter_status[$id] = 0;
				}
				$service_filter_status = $new_filter_status;
			}
			$reports = $db->query('UPDATE avail_config SET host_filter_status = '.$db->escape(serialize($host_filter_status)).', service_filter_status = '.$db->escape(serialize($service_filter_status)).' WHERE id = '.$db->escape($report->id));
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
		@exec('cp -p /etc/op5/auth_users.yml /etc/op5/auth_users.yml.' . $now . ' 2> /dev/null');
		@exec('cp -p /etc/op5/auth_groups.yml /etc/op5/auth_groups.yml.' . $now . ' 2> /dev/null');

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
			$data['start_time'] = arr::search($data, 'time', 0);
			$end_time = ScheduleDate_Model::time_to_seconds(arr::search($data, 'time', '0')) + ScheduleDate_Model::time_to_seconds(arr::search($data, 'duration', '0'));
			$data['end_time'] = sprintf(
				'%02d:%02d:%02d',
				($end_time / 3600),
				($end_time / 60 % 60),
				($end_time % 60));
			$data['weekdays'] = arr::search($data, 'recurring_day', array());
			$data['months'] = arr::search($data, 'recurring_month', array());
			$data['downtime_type'] = arr::search($data, 'report_type', '');
			if ($data['downtime_type'])
				$data['objects'] = arr::search($data, $report[$data['report_type']], array());
			$data['author'] = $row['author'];
			$sd = new ScheduleDate_Model();
			$sd->edit_schedule($data, $row['id']);
		}
	}

	public function license_start() {
		$row = Database::instance()->query("SELECT MIN(timestamp) from report_data");
		if(!$row) {
			echo "";
			return;
		}
		$value = $row->result(false)->current();
		echo $value['timestamp'];
	}
}
