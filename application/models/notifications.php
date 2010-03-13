<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle notifications for hosts and services
 */
class Notifications_Model extends Model {

	public $sort_field ='next_check';
	public $sort_order='ASC';
	public $where='';
	public $num_per_page = false;
	public $offset = false;
	public $count = false;

	/**
	*	Fetch scheduled events
	*
	*/
	public function show_notifications($num_per_page=false, $offset=false, $count=false)
	{

		$db = new Database();
		$auth = new Nagios_auth_Model();

		$host_query = $auth->authorized_host_query();
		if ($host_query === true) {

			$num_per_page = (int)$num_per_page;

			# only use LIMIT when NOT counting
			if ($offset !== false)
				$offset_limit = $count!==false ? "" : " LIMIT " . $offset.", ".$num_per_page;
			else
				$offset_limit = '';
				//$offset_limit = $count!==false ? "" : " LIMIT ".$num_per_page;
			//echo 'offset_limit: '.$offset_limit;

			$where_string = (!empty($this->where)) ? 'WHERE '.$this->where : '';

			if (!empty($where_string)) {
				$where_string .= "\nAND ";
			} else {
				$where_string .= "\nWHERE ";
			}
			$where_string .= "contact_name != ''\n";
			$sql = "SELECT host_name, service_description, start_time, end_time, reason_type, state,
							contact_name, notification_type, output
							FROM notification ".$where_string."
							ORDER BY ".$this->sort_field." ".$this->sort_order.
							$offset_limit;

			$result = $db->query($sql);
			if ($count === true) {
				return count($result);
			}
			return $result->count() ? $result->result(): false;
		}
	}

	/**
	*	Wrapper method to fetch no of hosts in the scheduling queue
	*/
	public function count_notifications()
	{
		return self::show_notifications(false, false, true);
	}

}