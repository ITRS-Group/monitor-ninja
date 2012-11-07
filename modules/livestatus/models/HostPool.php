<?php


class HostPool extends BaseHostPool {
	static public function by_group( $group ) {
		$result = self::all();
		$result->reduceBy( new LivestatusFilterMatch( 'groups', $group, '>=' ) );
		return $result;
	}
}
