<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * CMD controller
 *
 * Requires authentication. See the helper nagioscmd for more info.
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Command_Controller extends Authenticated_Controller
{
	/**
	 * Executes custom commands and return output to ajax call.
	 *
	 * @return string
	 */
	public function exec_custom_command()
	{
		$this->auto_render=false;
		$command_name = $this->input->get('command', null);
		$table = $this->input->get('table', null);
		$key = $this->input->get('key', null);
		if(!$command_name || !$table || !$key) {
			echo "No object type or identifier were set. Aborting.";
			return;
		}
		// Stop redirects
		$object = ObjectPool_Model::pool($table)->fetch_by_key($key);
		if(!$object instanceof NaemonMonitoredObject_Model) {
			echo "No object type or identifier were set. Aborting.";
			return;
		}

		$result = $object->submit_custom_command($command_name);
		if(!$result['status']) {
			echo "Script failed:" . nl2br($result['output']);
			return;
		}
		echo nl2br($result['output']);
	}
}
