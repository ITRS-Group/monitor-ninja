<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * System controller
 * Requires authentication
 * Test with retreiving system data
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
