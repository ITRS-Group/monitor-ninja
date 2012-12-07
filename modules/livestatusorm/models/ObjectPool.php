
<?php


abstract class ObjectPool_Model extends BaseObjectPool_Model {
	public static function get_by_query( $query ) {
		$preprocessor = new LSFilterPP_Core();
		
		$parser = new LSFilter_Core($preprocessor, new LSFilterMetadataVisitor_Core());
		$metadata = $parser->parse( $query );
		
		$parser = new LSFilter_Core($preprocessor, new LSFilterSetBuilderVisitor_Core($metadata));
		return $parser->parse( $query );
	}
}
