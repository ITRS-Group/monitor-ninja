<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller to fetch data via Ajax calls
 * Requires authentication
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 *
 */
class Ajax_Controller extends Authenticated_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Ajax calls shouldn't be rendered. This doesn't, because some unknown
		 * magic doesn't render templates in ajax requests, but for debugging
		 */
		$this->auto_render = false;
	}

	/**
	*	fetch specific setting
	*/
	public function get_setting()
	{
		$type = $this->input->post('type', false);
		$page = $this->input->post('page', false);
		if (empty($type))
			return false;
		$type = trim($type);
		$page = trim($page);
		$data = Ninja_setting_Model::fetch_page_setting($type, $page);
		$setting = $data!==false ? $data->setting : false;
		return json::ok(array($type => json_decode($setting)));
	}

	/**
	*	Save a specific setting
	*/
	public function save_page_setting()
	{
		$type = $this->input->post('type', false);
		$page = $this->input->post('page', false);
		$setting = $this->input->post('setting', false);

		if (empty($type) || empty($page) || (empty($setting) && $setting !== "0"))
			return false;
		Ninja_setting_Model::save_page_setting($type, $page, $setting);
	}

	/**
	*	Fetch translated help text
	* 	Two parameters arre supposed to be passed through POST
	* 		* controller - where is the translation?
	* 		* key - what key should be fetched
	*/
	public function get_translation()
	{
		$controller = $this->input->post('controller', false);
		$key = $this->input->post('key', false);

		if (empty($controller) || empty($key)) {
			return false;
		}
		$controller = ucfirst($controller).'_Controller';
		$result = call_user_func(array($controller,'_helptexts'), $key);
		return $result;
	}

	/**
	*	Fetch available report periods for selected report type
	*/
	public function get_report_periods()
	{
		$type = $this->input->post('type', 'avail');
		if (empty($type))
			return false;

		$report_periods = Reports_Controller::_report_period_strings($type);
		$periods = false;
		if (!empty($report_periods)) {
			foreach ($report_periods['report_period_strings'] as $periodval => $periodtext) {
				$periods[] = array('optionValue' => $periodval, 'optionText' => $periodtext);
			}
		} else {
			return false;
		}

		# add custom period
		$periods[] = array('optionValue' => 'custom', 'optionText' => "* " . _('CUSTOM REPORT PERIOD') . " *");

		echo json_encode($periods);
	}
}
