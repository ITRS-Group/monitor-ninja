<?php


abstract class ObjectPool_Model extends BaseObjectPool_Model {
	/* FIXME: saved queries shouldn't be hard coded... for testing now */
	private static $saved_queries = array(
			'hosts' => array(
					'std host state up'          => '[hosts] state=0 and has_been_checked=1',
					'std host state down'        => '[hosts] state=1 and has_been_checked=1',
					'std host state unreachable' => '[hosts] state=2 and has_been_checked=1',
					'std host pending'           => '[hosts] has_been_checked=0',
					'std host all'               => '[hosts] state!=999'
			),
			'services' => array(
					'std service state ok'       => '[services] state=0 and has_been_checked=1',
					'std service state warning'  => '[services] state=1 and has_been_checked=1',
					'std service state critical' => '[services] state=2 and has_been_checked=1',
					'std service state unknown'  => '[services] state=3 and has_been_checked=1',
					'std service pending'        => '[services] has_been_checked=0',
					'std service all'            => '[services] description!=""'
			)
	);

	public static function get_by_query( $query ) {
		$preprocessor = new LSFilterPP_Core();

		$parser = new LSFilter_Core($preprocessor, new LSFilterMetadataVisitor_Core());
		$metadata = $parser->parse( $query );

		$parser = new LSFilter_Core($preprocessor, new LSFilterSetBuilderVisitor_Core($metadata));
		return $parser->parse( $query );
	}

	public function get_by_name( $name ) {
		if( !isset( self::$saved_queries[$this->table] ) ) return false;
		if( !isset( self::$saved_queries[$this->table][$name] ) ) return false;
		return self::get_by_query(self::$saved_queries[$this->table][$name]);
	}
}
