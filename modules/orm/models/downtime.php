<?php

require_once( dirname(__FILE__).'/base/basedowntime.php' );

/**
 * Describes a single object from livestatus
 */
class Downtime_Model extends BaseDowntime_Model {
	static public $rewrite_columns = array(
		'triggered_by_text' => array('triggered_by')
		);
	
	/**
	 * Create an instance of the given type. Don't call dirctly, called from *Set_Model-objects
	 */
	public function __construct($values, $prefix) {
		parent::__construct($values, $prefix);
		$this->export[] = 'triggered_by_text';
	}
	
	/**
	 * Get triggered by object, as a text.
	 */
	public function get_triggered_by_text() {
		// TODO: Don't nest queries... Preformance!!! (Do this in livestatus?)
		$trig_id = $this->get_triggered_by();
		if( !$trig_id ) return 'N/A';
		$trig = DowntimePool_Model::all()->reduce_by('id', $trig_id, '=')->it(array('host.name', 'service.description'), array(), 1, 0)->current();
		if( !$trig ) return 'Unknown';
		$host = $trig->get_host()->get_name();
		$svc = $trig->get_service()->get_description();
		if( $svc ) return $host.';'.$svc;
		return $host;
	}
}
