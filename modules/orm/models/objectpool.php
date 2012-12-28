<?php

require_once( dirname(__FILE__).'/base/baseobjectpool.php' );

abstract class ObjectPool_Model extends BaseObjectPool_Model {

	public static function get_by_query( $query ) {
		$preprocessor = new LSFilterPP_Core();

		$parser = new LSFilter_Core($preprocessor, new LSFilterMetadataVisitor_Core());
		$metadata = $parser->parse( $query );

		$parser = new LSFilter_Core($preprocessor, new LSFilterSetBuilderVisitor_Core($metadata));
		return $parser->parse( $query );
	}

	public function get_by_name( $name ) {
		$query = LSFilter_Saved_Queries_Model::get_query($name,$this->table);
		if( $query === false ) return false;
		return self::get_by_query($query);
	}
}
