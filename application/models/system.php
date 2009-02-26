<?php defined('SYSPATH') OR die('No direct access allowed.');

class System_Model extends Ninja_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function rpm_info($filter = 'op5')
	{
		$filter = addslashes(trim($filter));
		$rpm_info = false;
		$exec_str = 'rpm -q';
		$exec_str .= !empty($filter) ? '|grep '.$filter.'|sort' : '|sort';
		exec($exec_str, $output, $retval);
		if ($retval==0 && !empty($output)) {
			foreach ($output as $rpm) {
				$rpm_info .= $rpm."<br />";
			}
			if (!empty($rpm_info)) {
				return $rpm_info;
			}
		}
		return false;
	}
}