<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * 	CLI controller for command line access to Ninja
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 * @copyright 2009 op5 AB
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
	}

	/**
	*	Takes input from commandline import of cgi.cfg
	*/
	public function _edit_user_authorization($username=false, $options=false)
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
	public function get_cgi_config()
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

	/**
	 * Insert user data from cgi.cfg into db
	 */
	public function insert_user_data()
	{
		# first import new users from cgi.cfg if there is any
		$path = realpath(APPPATH."../cli-helpers/htpasswd-import.php");
		require_once($path);
		$passwd_import = new htpasswd_importer();
		$passwd_import->overwrite = true;
		$base_path = System_Model::get_nagios_base_path();
		$etc_path = Kohana::config('config.nagios_etc_path') ? Kohana::config('config.nagios_etc_path') : $base_path.'/etc';
		if (substr($etc_path, -1, 1) != '/') {
			$etc_path .= '/';
		}
		$passwd_import->import_hashes($etc_path.'htpasswd.users');

		# don't assume any authorized users - start by removing all auth data
		User_Model::truncate_auth_data();

		if (empty($passwd_import->passwd_ary)) {
			# this is really bad since no users were found.
			# It could mean that this system is using some other means of authorization
			# (like LDAP?) but if we end up here something else in the configuration
			# is terribly wrong.
			return false;
		}
		$config_data = self::get_cgi_config();
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
				foreach (array_keys($passwd_import->passwd_ary) as $username) {
					self::_edit_user_authorization($username, array(1, 1, 1, 1, 1, 1, 1));
				}
			} else {
				$auth_users = false;
				foreach ($auth_fields as $field) {
					if (isset($config_data['user_data']['authorized_for_'.$field])) {
						foreach ($config_data['user_data']['authorized_for_'.$field] as $username) {
							if ($username === '*') {
								foreach (array_keys($passwd_import->passwd_ary) as $u) {
									$auth_users[$u][$field] = 1;
								}
							} else {
								# named user - set current access right
								$auth_users[$username][$field] = 1;
								foreach (array_keys($passwd_import->passwd_ary) as $u) {
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
						foreach (array_keys($passwd_import->passwd_ary) as $u) {
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
	}

}