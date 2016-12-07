<?php defined('SYSPATH') OR die('No direct access allowed.');
require_once('op5/auth/Auth.php');
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
		$op5_auth->force_user(new User_AlwaysAuth_Model());
		$this->auto_render=false;
	}

	private function _handle_nacoma_trigger($type, $old_name, $new_name = null) {
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}
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
				if($new_name !== null) {
					$needs_save = $report_data->rename_object($type, $old_name, $new_name);
				} else {
					$needs_save = $report_data->remove_object($type, $old_name);
				}
				if($needs_save) {
					$report_data->save();
				}
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
}
