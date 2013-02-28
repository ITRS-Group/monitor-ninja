<?php

require_once( dirname(__FILE__).'/base/baseobjectpool.php' );

/**
 * The univese of a objects of a given type in livestatus
 */
abstract class ObjectPool_Model extends BaseObjectPool_Model {

	/**
	 * Parse a query and return the related set
	 */
	public static function get_by_query( $query ) {
		$preprocessor = new LSFilterPP_Core();

		$parser = new LSFilter_Core($preprocessor, new LSFilterMetadataVisitor_Core());
		$metadata = $parser->parse( $query );

		$parser = new LSFilter_Core($preprocessor, new LSFilterSetBuilderVisitor_Core($metadata));
		return $parser->parse( $query );
	}

	/**
	 * Get a set of object, given a named query. Can be overridden to handle groups (in HostPool/ServicePool)
	 */
	public function get_by_name( $name ) {
		$query = LSFilter_Saved_Queries_Model::get_query($name,$this->table);
		if( $query === false ) return false;
		return self::get_by_query($query);
	}
	
	/**
	 * Get classes for tables in the system
	 */
	static public function load_table_classes() {
		return Module_Manifest_Model::get('orm_table_classes');
	}
	
	/**
	 * Get JS-files to render the orm...
	 * 
	 * @todo move from this class... should be somewhere else, but not here...
	 */
	static public function get_js_files() {
		$js_files = array();
		foreach( scandir(MODPATH) as $module ) {
			$path = MODPATH . $module . '/js/orm_structure.js';
			if( is_file($path) ) {
				$js_files[] = 'modules/'.$module.'/js/orm_structure.js';
			}
		}
		return $js_files;
	}
}
