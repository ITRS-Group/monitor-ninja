<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * display configurations
 */
class Config_Model extends Model {

	private $limit = 1000;
	private $offset = 0;

	/**
	 * Sets how many objects show_schduling_queue should return
	 *
	 * @param $limit
	 * @param $offset
	 */
	public function set_range( $limit, $offset ) {
		$this->limit = $limit;
		$this->offset = $offset;
	}

	/**
	 Workaround for PDO queries: runs $db->query($sql), copies
	 the resultset to an array, closes the resultset, and returns
	 the array.
	 */
	private function query($db,$sql)
	{
		$res = $db->query($sql);
		if (!$res)
			return NULL;

		$rc = array();
		foreach($res as $row) {
			$rc[] = $row;
		}
		unset($res);
		return $rc;
	}

	/**
	 * Fetch config info for a specific type
	 * @param $type The object type
	 * @param $free_text Only fetch items that match this free text
	 * @return If count is false, database object or false on error or empty. If count is true, number
	 */
	public function list_config($type = 'hosts', $free_text=null)
	{
		$options = array(
			'limit' => $this->limit + $this->offset,
			'offset' => $this->offset
		);

		switch($type) {
			case 'hosts':
				$options['extra_columns'] = array(
					'contact_groups',
					'event_handler',
					'contacts'
				);
				break;
			case 'services':
				$options['extra_columns'] = array(
					'active_checks_enabled',
					'accept_passive_checks',
					'check_freshness',
					'contact_groups',
					'contacts'
				);
				break;
			case 'contactgroups':
				$options['columns'] = array(
					'name',
					'alias',
					'members'
				);
				break;
		}

		if (!Auth::instance()->authorized_for('host_view_all')) {
			return false;
		}

		if($type != 'timeperiods') {
			return Livestatus::instance()->{'get'.$type}($options);
		}

		$db = Database::instance();
		$table = "timeperiod";
		$primary = "timeperiod_name";
		$sql = "SELECT
				timeperiod_name,
				alias,
				monday,
				tuesday,
				wednesday,
				thursday,
				friday,
				saturday,
				sunday
			FROM
				timeperiod";

		if($free_text) {
			$sql .= " WHERE $primary LIKE '%$free_text%'";
		}
		$sql .= "ORDER BY
				timeperiod_name";

		return $this->query($db,$sql);
	}
}
