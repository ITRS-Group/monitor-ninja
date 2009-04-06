<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * System controller
 * Requires authentication
 * Test with retreiving system data
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class System_Controller extends Authenticated_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function rpm_info($filter = 'op5')
	{
		$data = System_Model::rpm_info($filter);
		if (!empty($data)) {
			echo $data;
		} else {
			echo "No data found";
		}
	}
}
