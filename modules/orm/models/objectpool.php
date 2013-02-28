<?php

require_once( dirname(__FILE__).'/base/baseobjectpool.php' );

/**
 * The univese of a objects of a given type in livestatus
 */
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
	
	static public function load_table_classes() {
		return Module_Manifest_Model::get('orm_table_classes');
	}
	
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
