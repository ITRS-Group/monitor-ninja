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
	public function fetch_comments($host=false, $service=false, $num_per_page=false, $offset=false, $count=false)
	{
		$host = trim($host);
		$service = trim($service);
		if (empty($host)) {
			return false;
		}
		if (empty($service)) {
			$service = '';
		}
		$num_per_page = (int)$num_per_page;
		if ($count === false) {
			$data = ORM::factory('comment')
				->where(
					array(
						'host_name'=> $host,
						'service_description' => $service
					)
				)
				->find_all($num_per_page,$offset);
		} else {
			$data = ORM::factory('comment')
				->where(
					array(
						'host_name'=> $host,
						'service_description' => $service
					)
				)
				->find_all();
		}
		return $data ? $data : false;
	}

	/**
	*	Wrapper method to fetch nr of comments for host or service
	*/
	public function count_comments($host=false, $service=false)
	{
		return self::fetch_comments($host, $service, false, false, true);
	}

	/**
	*	Fetch all host- or service comments
	*/
	public function fetch_all_comments($host=false, $service=false, $num_per_page=false, $offset=false, $count=false)
	{
		$host = trim($host);
		$service = trim($service);
		$num_per_page = (int)$num_per_page;

		if ($count === false) {
			if (empty($service)) {
				$data = ORM::factory('comment')
					->where("host_name!='' AND (service_description='' OR service_description is null) ")
					->orderby('host_name')
					->find_all($num_per_page,$offset);
			} else {
				$data = ORM::factory('comment')
					->where("host_name!='' AND service_description!=''")
					->orderby(array('host_name' => 'ASC', 'service_description' => 'ASC'))
					->find_all($num_per_page,$offset);
			}
		} else {
			if (empty($service)) {
				$data = ORM::factory('comment')
					->where("host_name!='' AND (service_description='' OR service_description is null) ")
					->orderby('host_name')
					->find_all()->count();
			} else {
				$data = ORM::factory('comment')
					->where("host_name!='' AND service_description!=''")
					->orderby(array('host_name' => 'ASC', 'service_description' => 'ASC'))
					->find_all()->count();
			}
			return $data;
		}
		return $data ? $data : false;
	}

	/**
	*	Wrapper method to fetch a count of all service- or host comments
	*/
	public function count_all_comments($host=false, $service=false)
	{
		return self::fetch_all_comments($host, $service, false, false, true);
	}
}
