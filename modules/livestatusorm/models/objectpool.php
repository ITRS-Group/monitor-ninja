<?php


abstract class ObjectPool_Model extends BaseObjectPool_Model {
	/* FIXME: saved queries shouldn't be hard coded... for testing now */
	private static $saved_queries = array(
			'hosts' => array(
					'problem' => '[hosts] state>0'
					)
			);
	
	public static function get_by_query( $query ) {
		$preprocessor = new LSFilterPP_Core();

		$parser = new LSFilter_Core($preprocessor, new LSFilterMetadataVisitor_Core());
		$metadata = $parser->parse( $query );

		$parser = new LSFilter_Core($preprocessor, new LSFilterSetBuilderVisitor_Core($metadata));
		return $parser->parse( $query );
	}
	
	public static function get_by_name( $name, $table ) {
		if( !isset( self::$saved_queries[$table] ) ) return false;
		if( !isset( self::$saved_queries[$table][$name] ) ) return false;
		return self::get_by_query(self::$saved_queries[$table][$name]);
	}
}
