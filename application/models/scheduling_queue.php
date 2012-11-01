<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Handle comments for hosts and services
 */
class Scheduling_queue_Model extends Model {

	private $limit = 1000;
	private $offset = 0;
	
	public function set_range( $limit, $offset ) {
		$this->limit = $limit;
		$this->offset = $offset;
	}
	
	/**
	 * Fetch scheduled events
	 *
	 * @param $service_filter string = null
	 * @param $host_filter string = null
	 * @return Database result object or false if none if $count is false or unset, otherwise the number of result rows
	 */
	public function show_scheduling_queue($service_filter = null, $host_filter = null)
	{
		$result = array();
		$ls = Livestatus::instance();

		$max_objects = $this->limit + $this->offset; /* At most object needed to be fetched */
		
		$service_options = array(
			'columns' => array(
				'host_name',
				'description',
				'last_check',
				'next_check',
				'check_type', // 0 == active, 1 == passive
				'active_checks_enabled'
			),
			'filter' => array( 'should_be_scheduled' => 1 ),
			'limit' => $max_objects,
			'order' => array( 'next_check' => 'asc' )
		);
		
		if($service_filter) {
			$service_options['filter']['description'] = array("~~" => ".*$service_filter.*");
		}
		if($host_filter) {
			$service_options['filter']['host_name'] = array("~~" => ".*$host_filter.*");
		}
		$service_checks = $ls->getServices($service_options);

		$host_options = $service_options;
		$host_options['columns'] = array(
			'name',
			'last_check',
			'next_check',
			'check_type', // 0 == active, 1 == passive
			'active_checks_enabled'
		);
		if($host_filter) {
			$host_options['filter'] = array(
				'host_name' => array("~~" => ".*$host_filter.*")
			);
		} else {
			unset($host_options['filter']);
		}
		$host_checks = $ls->getHosts($host_options);
		if(!$host_checks && !$service_checks) {
			return array();
		}
		
		/* Do merge */
		$host_ptr = 0;
		$service_ptr = 0;
		$output = array();
		for( $i=0; $i<$this->limit + $this->offset; $i++ ) {
			if( $host_checks[$i]['next_check'] > $host_checks[$i]['next_check'] ) {
				/* Service */
				if( $i >= $this->offset )
					$output[] = (object)$service_checks[$i];
				$service_ptr++;
			} else {
				/* Host */
				if( $i >= $this->offset )
					$output[] = (object)$host_checks[$i];
				$host_ptr++;
			}
		}
		
		return $output;
	}
}
