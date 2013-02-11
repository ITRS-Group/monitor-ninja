<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Helper for commands, in particular for command authorization
 */
class commands_Core {
	/**
	 * Given the name of a command, return the type of the command
	 * (which can beharder than you'd think, see for example
	 * "ENABLE_SERVICEGROUP_HOST_CHECKS"), where "system" is code for
	 * "can't tell".
	 */
	public static function get_command_type($cmd)
	{
		$pos = array(
			'host' => strpos($cmd, '_HOST_'),
			'service' => strpos($cmd, '_SVC_') ?: strpos($cmd, '_SERVICE_'),
			'hostgroup' => strpos($cmd, '_HOSTGROUP_'),
			'servicegroup' => strpos($cmd, '_SERVICEGROUP_'),
			'system' => false,
		);
		$type = array_reduce(array_keys($pos), function ($a, $b) use ($pos) {
			return ($pos[$a] === false ? $b :
			($pos[$b] === false ? $a :
			($pos[$a] < $pos[$b] ? $a : $b)));
		}, 'system');
		return $type;
	}

	/**
	 * Check if user is authorized for the selected command
	 * http://nagios.sourceforge.net/docs/3_0/configcgi.html controls
	 * the correctness of this method
	 * Return codes:
	 *	-1:		No command passed
	 *	-2:		Contact can't submit commands
	 *	-3:		not authorized from cgi.cfg, and not a configured contact
	 * false:		fallthrough, not authorized for anything
	 */
	public static function is_authorized_for($params, $cmd = false)
	{
		$type = false;
		$cmd = isset($params['cmd_typ']) ? $params['cmd_typ'] : $cmd;

		# first see if this is a contact and, if so, if that contact
		# is allowed to submit commands. If it isn't, we can bail out
		# early.
		# FIXME: this is stupid
		$contact = ContactPool_Model::get_current_contact();
		if ($contact !== false) {
			if (!$contact->get_can_submit_commands()) {
				return -2;
			}
		}

		$objects = array(
			'service' => arr::search($params, 'service'),
			'host' => arr::search($params, 'host_name', array()),
			'hostgroup' => arr::search($params, 'hostgroup_name'),
			'servicegroup' => arr::search($params, 'servicegroup_name'),
		);

		$type = self::get_command_type($cmd);
		if ($type === 'system') {
			foreach ($objects as $k => $obj) {
				if ($obj) {
					$type = $k;
					break;
				}
			}
		}

		$user = Auth::instance()->get_user();

		# second we check if this contact is allowed to submit
		# the type of command we're looking at and, if so, if
		# we can bypass fetching all the objects we're authorized
		# to see
		if ($type == 'system') {
			# No per-contact rights, you're in or your out
			return $user->authorized_for('system_commands');
		}
		else if ($user->authorized_for($type.'_edit_all')) {
			# All of this type, gogogo!
			return true;
		}
		else if (!$objects[$type] && $user->authorized_for($type.'_edit_contact')) {
			# a valid use-case is to see if the user would be allowed to submit this command
			# if we weren't told what objects they would try with, tell them asking is fine
			return true;
		}
		else if (!$user->authorized_for($type.'_edit_contact')) {
			# if we don't have by contact, just give up
			return false;
		}

		# not authorized from cgi.cfg, and not a configured contact,
		# so bail out early
		if ($contact === false)
			return -3;

		if ($objects['service']) {
			if (!is_array($objects['service']))
				$objects['service'] = array($objects['service']);
			foreach ($objects['service'] as $service) {
				if (strstr($service, ';')) {
					# we have host_name;service in service field
					$parts = explode(';', $service, 2);
				}
				else {
					$parts = array(end($objects['host']), $service);
				}
				if (!$user->authorized_for_object('service', $parts))
					return false;
			}
			return true;
		}
		if (!is_array($objects[$type]))
			$objects[$type] = array($objects[$type]);
		foreach ($objects[$type] as $object) {
			if (!$user->authorized_for_object($type, $object))
					return false;
		}
		return true;
	}
}
