<?php

require_once("ORMObjectSetGenerator.php");

class ORMLSSetGenerator extends ORMObjectSetGenerator {

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
	}

	public function generate_backend_specific_functions() {
	}

	/**
	 * Generate stats
	 *
	 * @return void
	 **/
	public function generate_stats() {
		$this->init_function('stats',array('intersections'));
		$this->write('$ls = op5livestatus::instance();');

		$this->write('$single = !is_array($intersections);');
		$this->write('if($single) $intersections = array($intersections);');

		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor(array(%s, "map_name_to_backend"));', $this->structure['class'].'Set'.self::$model_suffix);
		$this->write('$sb_visit = new LivestatusStatsBuilderVisitor(array(%s, "map_name_to_backend"));', $this->structure['class'].'Set'.self::$model_suffix);
		$this->write('$ls_filter = $this->filter->visit($fb_visit, false);');

		$this->write('$ls_intersections = array();');
		$this->write('foreach( $intersections as $name => $intersection ) {');
		$this->write('if($intersection->table == $this->table) {');
		$this->write('$ls_intersections[$name] = $intersection->filter->visit($sb_visit, false);');
		$this->write('}'); // TODO: Error handling...
		$this->write('}');

		$this->write('$result = $ls->stats_single($this->table, $ls_filter, $ls_intersections);');

		$this->write('if($single) $result = $result[0];');
		$this->write('return $result;');
		$this->finish_function();
	}

	/**
	 * Generate count
	 *
	 * @return void
	 **/
	public function generate_count() {
		$this->init_function('count');
		$this->write('$ls = op5livestatus::instance();');

		$this->write('$filter = $this->get_auth_filter();');
		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor(array(%s, "map_name_to_backend"));', $this->structure['class'].'Set'.self::$model_suffix);
		$this->write('$ls_filter = $filter->visit($fb_visit, false);');
		$this->write('$ls_filter .= "Limit: 0\n";');

		$this->write('$result = $ls->query($this->table, $ls_filter, false);');

		$this->write('return $result[2];');
		$this->finish_function();
	}

	/**
	 * Generates set
	 *
	 * @return void
	 **/
	public function generate_it() {
		$this->init_function( 'it', array('columns','order','limit','offset'), array(), array('order' => array(), 'limit'=>false, 'offset'=>false) );
		$this->write('$ls = op5livestatus::instance();');

		$this->write('$filter = $this->get_auth_filter();');
		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor(array(%s, "map_name_to_backend"));', $this->structure['class'].'Set'.self::$model_suffix);
		$this->write('$ls_filter = $filter->visit($fb_visit, false);');

		foreach(array('$order','$this->default_sort') as $sortfield) {
			$this->write('foreach('.$sortfield.' as $col_attr) {');
			$this->write(  '$parts = explode(" ",$col_attr);');
			$this->write(  '$parts[0] = static::map_name_to_backend($parts[0]);');
			/* Skip sort if not accepted column */
			$this->write(  'if($parts[0] !== false) {');
			$this->write(    '$parts = array_filter($parts);');
			$this->write(    '$ls_filter .= "Sort: ".implode(" ",$parts)."\n";');
			$this->write(  '}');
			$this->write('}');
		}

		$this->write('if( $offset !== false ) {');
		$this->write('$ls_filter .= "Offset: ".intval($offset)."\n";');
		$this->write('}');

		$this->write('if( $limit !== false ) {');
		$this->write('$ls_filter .= "Limit: ".intval($limit)."\n";');
		$this->write('}');

		$this->write('$valid_columns = false;');
		$this->write('if( $columns !== false ) {');
		$this->write(  '$processed_columns = array_merge($columns, $this->key_columns);');
		$this->write(  '$processed_columns = static::apply_columns_rewrite($processed_columns);');
		$this->write(  '$valid_columns = array();');
		$this->write(  'foreach($processed_columns as $col) {');
		$this->write(    '$new_name = static::map_name_to_backend($col);');
		$this->write(    'if($new_name !== false) {');
		$this->write(      '$valid_columns[] = $new_name;');
		$this->write(    '}');
		$this->write(  '}');
		$this->write(  '$valid_columns = array_unique($valid_columns);');
		$this->write('}');

		$this->write('try {');
		$this->write('list($fetched_columns, $objects, $count) = $ls->query($this->table, $ls_filter, $valid_columns);');
		$this->write('} catch( op5LivestatusException $e ) {');
		$this->write('throw new ORMException( $e->getPlainMessage() );');
		$this->write('}');

		$this->write('if($columns === false) {');
		$this->write(    '$columns = static::get_all_columns_list();');
		$this->write('}');

		$this->write('return new LivestatusSetIterator($objects, $fetched_columns, $columns, $this->class);');
		$this->finish_function();
	}

	/**
	 * Generate the method map_name_to_backend for the object set
	 *
	 * @param $oset ORMObjectSetGenerator
	 */
	public function generate_map_name_to_backend() {
		$this->init_function('map_name_to_backend', array('name', 'prefix'), array('static'), array('prefix' => false));
		$this->write('if($prefix === false) {');
		$this->write('$prefix = "";');
		$this->write('}');
		foreach($this->structure['structure'] as $field => $type ) {
			$backend_field = $field;
			if(isset($this->structure['rename']) && isset($this->structure['rename'][$field])) {
				$backend_field = $this->structure['rename'][$field];
			}
			if(is_array($type)) {
				$subobjset_class = $type[0].'Set'.self::$model_suffix;
				$this->write('if(substr($name,0,%s) == %s) {', strlen($field)+1, $field.'.');
				$this->write('return '.$subobjset_class.'::map_name_to_backend(substr($name,%d),%s);', strlen($field)+1, $type[1]);
				$this->write('}');
			} else {
				$this->write('if($name == %s) {', $field);
				$this->write('return $prefix.%s;',$backend_field);
				$this->write('}');
			}
		}
		$this->write('return false;');
		$this->write('}');
	}
}
