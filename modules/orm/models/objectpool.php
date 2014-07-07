<?php

require_once( dirname(__FILE__).'/base/baseobjectpool.php' );

/**
 * The univese of a objects of a given type in livestatus
 */
abstract class ObjectPool_Model extends BaseObjectPool_Model {

	/**
	 * List which places this object is available for.
	 *
	 * It is (for now) always available within ninja. Other places are: api
	 * @var array
	 */
	protected static $available_for = array(
		'api' => true
	);

	public function available_for($where) {
		if(isset(static::$available_for[$where])) {
			return static::$available_for[$where];
		}
		/*
		 * Everything has traditionally been available everywhere. This is to limit
		 * access if we can't guarantee integrety of the API in upcoming releases
		 */
		return true;
	}


	/**
	 * Parse a query and return the related set
	 */
	public static function get_by_query( $query, $disabled_saved_queries = array() ) {
		$preprocessor = new LSFilterPP();

		$parser = new LSFilter($preprocessor, new LSFilterMetadataVisitor());
		$metadata = $parser->parse( $query );

		$parser = new LSFilter($preprocessor, new LSFilterSetBuilderVisitor($metadata, $disabled_saved_queries));
		return $parser->parse( $query );
	}

	/**
	 * Get a set of object, given a named query. Can be overridden to handle groups (in HostPool/ServicePool)
	 */
	public function get_by_name( $name, $disabled_saved_queries = array() ) {
		if( in_array($name, $disabled_saved_queries) ) {
			throw new ORMException( 'Circular depencencies of saved filters detected');
		}
		$query = LSFilter_Saved_Queries_Model::get_query($name,$this->table);
		if( $query === false ) return false;

		$disabled_saved_queries[] = $name;
		return self::get_by_query($query, $disabled_saved_queries);
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
