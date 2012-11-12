<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieve and manipulate service status data
 */
class Custom_variable_Model extends Model
{
	/**
	 * @param $object_type string
	 * @param $object_id int = null
	 * @throws InvalidArgumentException
	 * @return array
	 */
	public static function get_for($object_type, $object_id = null) {
		switch($object_type) {
			case 'host':
			case 'service':
				break;
			default:
				throw new InvalidArgumentException("'$object_type' is not a valid object type in ".__METHOD__);
		}
		if($object_id) {
			return Database::instance()->query("SELECT * FROM custom_vars WHERE obj_type = ? AND obj_id = ?", array($object_type, $object_id))->as_array(false);
		}
		return Database::instance()->query("SELECT * FROM custom_vars WHERE obj_type = ?", array($object_type))->as_array(false);
	}

	/**
	 * @param $custom_variables array
	 * @return array
	 */
	public static function parse_custom_variables($custom_variables) {
		$custom_commands = array();
		if (count($custom_variables) >= 2) {
			foreach ($custom_variables as $custom_variable) {
				// Does custom variable name match pattern?
				if (substr($custom_variable['variable'], 0, 4) === '_OP5') {
					$parts = explode('__', $custom_variable['variable']);
					$command_name = $parts[count($parts)-1];
					if (!isset($custom_commands[$command_name])) {
						$custom_commands[$command_name] = array();
					}
					if (in_array('ACCESS', $parts)) {
						$custom_commands[$command_name]['access'] = explode(',',$custom_variable['value']);
					}
					if (in_array('ACTION', $parts)) {
						$custom_commands[$command_name]['action'] = $custom_variable['value'];
					}
				}
				// Add additional special cases here.
			}
		}
		if (count($custom_commands) > 0) {
			foreach ($custom_commands as $command_name => $params) {
				$authenticated = false;
				// Check authorization.
				$members = array();
				foreach ($custom_commands[$command_name]['access'] as $contactgroup) {
					$members[$contactgroup] = Contactgroup_Model::is_user_member($contactgroup);
					if ($members[$contactgroup] === true) {
						$authenticated = true;
					}
				}
				if ($authenticated) {
					$custom_commands[$command_name] = $custom_commands[$command_name]['action'];
				} else {
					unset($custom_commands[$command_name]);
				}
			}
		}
		return $custom_commands;
	}
}
