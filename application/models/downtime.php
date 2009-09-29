<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Fetch downtime info from downtime table
 */
class Downtime_Model extends Model
{

	/**
	*	Fetch current downtime information
	*/
	public function get_downtime_data()
	{
		$db = new Database();
		$auth = new Nagios_auth_Model();

		$host_query = $auth->authorized_host_query();
		if ($host_query === true) {
			# don't use auth_host fields etc
			$auth_host_alias = 'h';
			$auth_from = '';
			$auth_where = '';
		} else {
			$auth_host_alias = $host_query['host_field'];
			$auth_from = ' ,'.$host_query['from'];
			$auth_where = ' WHERE '.sprintf($host_query['where'], "d.host_name");
		}
		$sql = "SELECT d.* FROM downtime AS d ".$auth_from.$auth_where;
		$result = $db->query($sql);
		return $result->count() ? $result: false;
	}
}