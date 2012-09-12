<?php

/**
 * A model for generating statistics from livestatus
 */
class Stats_Model extends Model {
	public function get_stats($type, $options = null, $last_program_start = null) {
		try {
			$ls = Livestatus::instance();
			switch($type) {
				case 'host_totals':		return $ls->getHostTotals($options);
								break;
				case 'service_totals':		return $ls->getServiceTotals($options);
								break;
				case 'host_performance':	return $ls->getHostPerformance($last_program_start, $options);
								break;
				case 'service_performance':	return $ls->getServicePerformance($last_program_start, $options);
								break;
				default:			throw new Exception("unknown type: $type");
								break;
			}
		} catch (LivestatusException $ex) {
			return false;
		}
	}
}
