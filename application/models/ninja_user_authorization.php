<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * This model takes care of the ninja authorization data
 * Insert/update/fetch
 */
class Ninja_user_authorization_Model extends Model
{
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
		$db = new Database();
		$sql = "SELECT * FROM ninja_user_authorization WHERE user_id=".(int)$user_id;
		$res = $db->query($sql);

		if (count($res)!=0) {
			# user exists, update authorization data
			$sql = "UPDATE ninja_user_authorization SET ";
			$updates = false;
			foreach	($options as $field => $value) {
				$updates[] = $field.'='.$value;
			}
			$sql .= implode(',', $updates);
			$sql .= " WHERE user_id = $user_id";
		} else {
			# create new record
			$sql = "INSERT INTO ninja_user_authorization(".implode(',', array_keys($options)).", user_id) ".
				"VALUES(".implode(',', array_values($options)).", ".$user_id.")";
		}

		unset($res);
		# done, save it
		$db->query($sql);
		return true;
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
			$user = User_Model::get_user($username);
			if ($user != false) {
				$user_id = $user->id;
			} else
				return false;
		}
		$auth_data = false;

		# fetch auth data for the user_id
		$db = new Database();
		$sql = "SELECT * FROM ninja_user_authorization WHERE user_id=".(int)$user_id;
		$res = $db->query($sql);
		if (count($res)!=0) {
			$auth_fields = self::$auth_fields;
			$auth = $res->current();
			foreach ($auth_fields as $field) {
				if ($auth->{$field}) {
					$auth_data['authorized_for_'.$field] = $auth->{$field};
				}
			}
			unset($res);
			return $auth_data;
		}
		return false;
	}
}
