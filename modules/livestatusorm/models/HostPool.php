<?php


class HostPool extends BaseHostPool {
	static public function by_group( $group ) {
		$result = self::all();
		$result->reduceBy( 'groups', $group, '>=' );
		return $result;
	}
}
