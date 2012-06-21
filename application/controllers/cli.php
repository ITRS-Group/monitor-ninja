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
class Cli_Controller extends Authenticated_Controller {

	public function __construct()
	{
		# Only grant permission for cli access or if
		# user has been given the ADMIN role
		if (PHP_SAPI !== "cli" &&
			!Auth::instance()->logged_in(Ninja_Controller::ADMIN)) {
			url::redirect('default/index');
		}
		parent::__construct();
		$this->auto_render = false;
	}

	/**
	*	Takes input from commandline import of cgi.cfg
	*/
	public static function _edit_user_authorization($username=false, $options=false)
	{
		if (empty($username) || empty($options)) {
			return false;
		}
		$result = User_Model::user_auth_data($username, $options);
		return $result;
	}

	/**
	 * Parse input data from commandline and stores in an array
	 * An equivalent to getopt() but easier for us in this environment
	 */
	public function _parse_parameters($noopt = array())
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

	/**
	 * fetch data from cgi.cfg and return to calling script
	 */
	public static function get_cgi_config()
	{
		$auth_data = System_Model::parse_config_file('cgi.cfg');
		$user_data = false;
		$user_list = array();
		$return = false;
		$access_levels = array(
			'authorized_for_system_information',
			'authorized_for_configuration_information',
			'authorized_for_system_commands',
			'authorized_for_all_services',
			'authorized_for_all_hosts',
			'authorized_for_all_service_commands',
			'authorized_for_all_host_commands'
		);

		if(empty($auth_data)) {
			return false;
		}

		foreach($auth_data as $k => $v) {
			if(substr($k, 0, 14) === 'authorized_for') {
				$auth_data[$k] = explode(',', $v);
			}
		}

		# fetch defined access data for users
		foreach ($access_levels as $level) {
			$users = $auth_data[$level];
			foreach ($users as $user) {
				$user_data[$level][] = $user;
				if (!in_array($user, $user_list)) {
					$user_list[] = $user;
				}
			}
		}
		if (array_key_exists('refresh_rate', $auth_data)) {
			$return['refresh_rate'] = $auth_data['refresh_rate'];
		}
		$return['user_data'] = $user_data;
		$return['user_list'] = $user_list;
		return $return;
	}

	private static function clean_old_users($old_ary, $new_ary)
	{
		$db = Database::instance();
		# check for users that has been removed
		foreach ($old_ary as $old => $skip) {
			if (!array_key_exists($old, $new_ary)) {
				# delete this user as it is no longer available in
				# the received list of users
				$db->query("DELETE FROM users ".
				" WHERE username=".$db->escape($old));
			}
		}
		$db->query('DELETE FROM roles_users WHERE user_id NOT IN (SELECT id FROM users)');
	}

	/**
	 * Insert user data from cgi.cfg into db
	 */
	public static function insert_user_data()
	{
		$auth_types = Kohana::config('auth.auth_methods');
		if (!is_array($auth_types))
			$auth_types = array($auth_types => $auth_types);

		// We *only* want LDAP auth, we aren't running this on CLI, and we do allow
		// CLI access - we can skip this, and let update-users.php deal with it.
		if (count($auth_types) === 1 && isset($auth_types['LDAP']) &&
			Kohana::config('config.cli_access') !== false &&
			PHP_SAPI !== 'cli')
		{
			return true;
		}

		# don't assume any authorized users - start by removing all auth data
		User_Model::truncate_auth_data();

		$passwd_import = new Htpasswd_importer_Model();

		$passwd_import->get_existing_users();
		$old_users = $passwd_import->existing_ary;
		$new_users = array();

		$config_data = self::get_cgi_config();

		$abort = true;
		if (isset($auth_types['LDAP'])) {
			$contacts = Contact_Model::get_contact_names();
			foreach ($contacts as $name) {
				User_Model::add_user(array('username' => $name));
			}

			if (isset($config_data['user_list']) && !empty($config_data['user_list'])) {
				# We need to make sure LDAP/AD users exists in merlin.users
				foreach ($config_data['user_list'] as $user) {
					if ($user) {
						User_Model::add_user(array('username' => $user));
						$new_users[$user] = 1;
					}
				}
			}
			$abort = false;
		}

		if (count($auth_types) > 1 || !isset($auth_types['LDAP'])) {
			# first import new users from cgi.cfg if there is any
			$passwd_import->overwrite = true;
			$base_path = System_Model::get_nagios_base_path();
			$etc_path = Kohana::config('config.nagios_etc_path') ? Kohana::config('config.nagios_etc_path') : $base_path.'/etc';
			if (substr($etc_path, -1, 1) != '/') {
				$etc_path .= '/';
			}
			$passwd_import->import_hashes($etc_path.'htpasswd.users');


			if (!empty($passwd_import->passwd_ary)) {
				$new_users = array_merge($new_users, $passwd_import->passwd_ary);
				$abort = false;
			}
		}
		if ($abort)
			return false;

		self::clean_old_users($old_users, $new_users);

		# fetch all usernames from users table
		$users = User_Model::get_all_usernames();

		# there are no users in db
		if ($users == false) {
			return false;
		}

		# All db fields that should be set
		# according to data in cgi.cfg
		$auth_fields = Ninja_user_authorization_Model::$auth_fields;
		if (empty($config_data['user_list'])) {
			return false;
		} else {
			if (in_array('*', $config_data['user_list']) && sizeof($config_data['user_list'])==1) {
				# we don't have named users from cgi.cfg but ONLY one item
				# since all users has been assigned all access rights (*)
				# Instead we use the list from the htpasswd importer
				# and assign all user the correct Nagios credentials
				foreach ($users as $username) {
					self::_edit_user_authorization($username, array(1, 1, 1, 1, 1, 1, 1));
				}
			} else {
				$auth_users = false;
				foreach ($auth_fields as $field) {
					if (isset($config_data['user_data']['authorized_for_'.$field])) {
						foreach ($config_data['user_data']['authorized_for_'.$field] as $username) {
							if ($username === '*') {
								foreach ($users as $u) {
									$auth_users[$u][$field] = 1;
								}
							} else {
								# named user - set current access right
								$auth_users[$username][$field] = 1;
								foreach ($users as $u) {
									# check all other users
									if ($u === $username || isset($auth_users[$u][$field])) {
										# discard users already been checked for this access right
										continue;
									} else {
										# prevent the rest from getting this access
										$auth_users[$u][$field] = 0;
									}
								} # end foreach
							}
						} # end foreach
					} else {
						# unset access rights for all users for this key
						foreach ($users as $u) {
							$auth_users[$u][$field] = 0;
						}
					}
				}
				if (!empty($auth_users)) {
					# take all the users in auth_users where keys are usernames
					# and contained array has 'autorized_for'<field> as key and boolean
					# value indicatingn if user has this access or not
					foreach ($auth_users as $user => $options) {
						self::_edit_user_authorization($user, array_values($options));
					}
				} else {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * When an object is renamed, things like scheduled reports and rrdtool data must be renamed as well
	 */
	public function handle_rename($type, $old_name, $new_name)
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

		# figure out path from argv
		$path = $GLOBALS['argv'][0];

		$user = false;
		if ($cli_access == 1) {
			exec('/usr/bin/php '.$path.' default/get_a_user ', $user, $retval);
			$user = $user[0];
		} else {
			# username is hard coded so let's use this
			$user = $cli_access;
		}
		if (empty($user)) {
			# we failed to detect a valid user so there's no use in continuing
			return false;
		}

		// Saved reports:
		$saved_reports_model = new Saved_reports_Model();
		$report_types = array('avail', 'sla');
		foreach ($report_types as $report_type) {
			$reports = $saved_reports_model->get_saved_reports($report_type, $user);
			foreach ($reports as $report) {
				$report_data = $saved_reports_model->get_report_info($report_type, $report->id);
				if ($report_data['report_type'] === 'services' && $type === 'host') {
					$savep = false;
					foreach ($report_data['objects'] as $idx => $svc) {
						$parts = explode(';', $svc);
						if ($parts[0] === $old_name) {
							$report_data['objects'][$idx] = $new_name.';'.$parts[1];
							$savep = true;
						}
					}
					if ($savep)
						$saved_reports_model->save_config_objects($report_type, $report->id, $report_data['objects']);
				}
				if ($report_data['report_type'] !== ($type . 's'))
					continue;
				$key = array_search($old_name, $report_data['objects']);
				if ($key === false)
					continue;
				$report_data['objects'][$key] = $new_name;
				$saved_reports_model->save_config_objects($report_type, $report->id, $report_data['objects']);
			}
		}
	}

	/**
	 * Perform post-deletion cleanup
	 */
	public function handle_deletion($type, $name)
	{
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
}
