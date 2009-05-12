<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle comments for hosts and services
 */
class Comment_Model extends ORM {
	protected $table_names_plural = false;
	protected $primary_key = 'id';

	/***************************** COMMENT TYPES *******************************/
	const HOST_COMMENT = 1;
	const SERVICE_COMMENT = 2;

	/****************************** ENTRY TYPES ********************************/
	const USER_COMMENT = 1;
	const DOWNTIME_COMMENT = 2;
	const FLAPPING_COMMENT = 3;
	const ACKNOWLEDGEMENT_COMMENT = 4;

	/**
	*	Fetch saved comments for host or service
	*
	*/
	public function fetch_comments($host=false, $service=false)
	{
		$host = trim($host);
		$service = trim($service);
		if (empty($host)) {
			return false;
		}
		if (empty($service)) {
			$service = '';
		}
		$data = ORM::factory('comment')
			->where(
				array(
					'host_name'=> $host,
					'service_description' => $service
				)
			)
			->find_all();
		return $data;//->loaded ? $data : false;
	}
}
