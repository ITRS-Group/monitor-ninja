<?php

require_once( dirname(__FILE__).'/base/basedowntime.php' );

/**
 * Describes a single object from livestatus
 */
class Downtime_Model extends BaseDowntime_Model {

	/**
	 * @ninja orm_command name Delete downtime
	 * @ninja orm_command category Operations
	 * @ninja orm_command icon delete-downtime
	 * @ninja orm_command mayi_method delete.command
	 * @ninja orm_command description
	 *     Delete/cancel a scheduled downtime entry.
	 * @ninja orm_command view monitoring/naemon_command
	 */
	public function delete() {
		$cmd = "DEL_HOST_DOWNTIME";
		if($this->get_is_service()) {
			$cmd = "DEL_SVC_DOWNTIME";
		}
		return $this->submit_naemon_command($cmd);
	}

	/**
	 * Get triggered by object, as a text.
	 *
	 * @ninja orm depend[] triggered_by
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
