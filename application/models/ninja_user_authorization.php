<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * This model takes care of the ninja authorization data
 * Insert/update/fetch
 */
class Ninja_user_authorization_Model extends ORM
{
	protected $table_names_plural = false;
	protected $primary_key = 'id';
	protected $has_many = array('users');
	public static $auth_fields = array(
				'system_information',
				'configuration_information',
				'system_commands',
				'all_services',
				'all_hosts',
				'all_service_commands',
				'all_host_commands'
			);

	/**
	*	This method is what actually takes care of the
	* 	insert/update of the auth_data.
	* 	If authorization credentials has changed for an existing
	* 	user, the data will be updated
	*/
	public function insert_user_auth_data($user_id=false, $options=false)
	{
		if (empty($user_id) || empty($options))
			return false;
		$user_id = (int)$user_id;

		# check if we already have any data
		$auth = ORM::factory('ninja_user_authorization')->where('user_id', $user_id)->find();
		if ($auth->loaded) {
			# user exists, update authorization data
			foreach	($options as $field => $value) {
				$auth->{$field} = $value;
			}
		} else {
			# create new record
			$auth->user_id = $user_id;
			foreach	($options as $field => $value) {
				$auth->{$field} = $value;
			}
		}

		# done, save it
		$auth->save();
		return $auth->saved;
	}

	/**
	*	fetch authorization data for a user identified by username
	*	or user_id. If both username and user_id are empty
	* 	we use the id of the current user.
	*/
	public function get_auth_data($username=false, $user_id=false)
	{
		if (empty($username) && empty($user_id)) {
			$user_id = Auth::instance()->get_user()->id;
		}
		if (empty($user_id)) {
			# fetch user_id
			if (empty($username))
				return false;
			$user = ORM::factory('user')->where('username', $username)->find();
			if ($user->loaded) {
				$user_id = $user->id;
			} else
				return false;
		}
		$auth_data = false;

		# fetch auth data for the user_id
		$auth = ORM::factory('ninja_user_authorization')->where('user_id', $user_id)->find();
		if ($auth->loaded) {
			$auth_fields = self::$auth_fields;
			foreach ($auth_fields as $field) {
				$auth_data['authorized_for_'.$field] = $auth->{$field};
			}
			return $auth_data;
		}
		return false;
	}
}
