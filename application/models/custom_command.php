<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *	Authorize and parse custom command.
 */
class Custom_command_Model extends Model
{
	/**
	 *	Parses custom variables and returns custom commands
	 *	@param $custom_variables array
	 *	@param $specific string
	 *	@return array
	 */
	public static function parse_custom_variables($custom_variables, $specific = false) {
		$custom_commands = array();
		// Need at least 2 custom variables for this to make any sense.
		if (count($custom_variables) >= 2) {
			foreach ($custom_variables as $key => $value) {
				// Does custom variable name match pattern?
				if (substr($key, 0, 4) === '_OP5') {
					$parts = explode('__', $key);
					$command_name = $parts[count($parts)-1];
					if ($specific !== false && $specific !== $command_name) {
						// We wanted a specific command which doesn't exist. Go to next loop.
						continue;
					}
					if (!isset($custom_commands[$command_name])) {
						$custom_commands[$command_name] = array();
					}
					if (in_array('ACCESS', $parts)) {
						$custom_commands[$command_name]['access'] = explode(',',$value);
					}
					if (in_array('ACTION', $parts)) {
						$custom_commands[$command_name]['action'] = $value;
					}
				}
			}
		}
		if (count($custom_commands) > 0) {
			foreach ($custom_commands as $command_name => $params) {
				if (isset($custom_commands[$command_name]['access']) && isset($custom_commands[$command_name]['action'])) {
					// Check authorization.
					$set = ContactGroupPool_Model::none();
					$all_set = ContactGroupPool_Model::all();
					foreach ($custom_commands[$command_name]['access'] as $contactgroup) {
						$set = $set->union($all_set->reduce_by('name', $contactgroup, "="));
					}
					$set = $set->reduce_by('members', Auth::instance()->get_user()->username, '>=');
					// If we got any matches set action, if not unset custom command
					if (count($set) > 0) {
						$custom_commands[$command_name] = $custom_commands[$command_name]['action'];
					} else {
						unset($custom_commands[$command_name]);
					}
				} else {
					// Incomplete custom command due to not having both ACCESS and ACTION with the same name. Unset
					unset($custom_commands[$command_name]);
				}
			}
		}
		return $custom_commands;
	}
}
