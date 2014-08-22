<?php

require_once(__DIR__."/../common/ORMObjectPoolGenerator.php");

class ORMLSObjectPoolGenerator extends ORMObjectPoolGenerator {
	public function generate_backend_specific_functions() {
		$this->generate_map_name_to_backend();
	}

	/**
	 * Generate stats
	 *
	 * @return void
	 **/
	public function generate_stats() {
		$this->init_function('stats',array('filter','intersections'), array('static'));
		$this->write('$ls = op5livestatus::instance();');

		$this->write('$single = !is_array($intersections);');
		$this->write('if($single) $intersections = array($intersections);');

		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor(array(%s, "map_name_to_backend"));', $this->pool_class);
		$this->write('$sb_visit = new LivestatusStatsBuilderVisitor(array(%s, "map_name_to_backend"));', $this->pool_class);
		$this->write('$ls_filter = $filter->visit($fb_visit, false);');

		$this->write('$ls_intersections = array();');
		$this->write('foreach( $intersections as $name => $intersection ) {');
		$this->write('if($intersection->table == %s) {', $this->name);
		$this->write('$ls_intersections[$name] = $intersection->filter->visit($sb_visit, false);');
		$this->write('}'); // TODO: Error handling...
		$this->write('}');

		$this->write('$result = $ls->stats_single(%s, $ls_filter, $ls_intersections);', $this->name);

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
		$this->init_function('count', array('filter'), array('static'));
		$this->write('$ls = op5livestatus::instance();');

		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor(array(%s, "map_name_to_backend"));', $this->pool_class);
		$this->write('$ls_filter = $filter->visit($fb_visit, false);');
		$this->write('$ls_filter .= "Limit: 0\n";');

		$this->write('$result = $ls->query(%s, $ls_filter, false);', $this->name);

		$this->write('return $result[2];');
		$this->finish_function();
	}

	/**
	 * Generates set
	 *
	 * @return void
	 **/
	public function generate_it() {
		$this->init_function( 'it', array('filter','columns','order','limit','offset'), array('static'), array('order' => array(), 'limit'=>false, 'offset'=>false) );
		$this->write('$ls = op5livestatus::instance();');

		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor(array(%s, "map_name_to_backend"));', $this->pool_class);
		$this->write('$ls_filter = $filter->visit($fb_visit, false);');

		foreach(array('$order','self::$default_sort') as $sortfield) {
			$this->write('foreach('.$sortfield.' as $col_attr) {');
			$this->write(  '$parts = explode(" ",$col_attr);');
			$this->write(  '$original_part_0 = $parts[0];');
			$this->write(  '$parts[0] = static::map_name_to_backend($parts[0]);');
			/* Throw exception if column is not found */
			$this->write(  'if($parts[0] === false) {');
			$this->write(    'throw new ORMException(%s.$original_part_0."\'");', "Table '".$this->name."' has no column '");
			$this->write(  '}');
			$this->write(  '$parts = array_filter($parts);');
			$this->write(  '$ls_filter .= "Sort: ".implode(" ",$parts)."\n";');
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
		$this->write(  '$processed_columns = array_merge($columns, %s);', $this->key);
		$this->write(  '$processed_columns = '.$this->set_class.'::apply_columns_rewrite($processed_columns);');
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
		$this->write('list($fetched_columns, $objects, $count) = $ls->query(%s, $ls_filter, $valid_columns);', $this->name);
		$this->write('} catch( op5LivestatusException $e ) {');
		$this->write('throw new ORMException( $e->getPlainMessage() );');
		$this->write('}');

		$this->write('if($columns === false) {');
		$this->write(    '$columns = static::get_all_columns_list();');
		$this->write('}');

		$this->write('return new LivestatusSetIterator($objects, $fetched_columns, $columns, %s);', $this->obj_class);
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
				$subobjpool_class = $type[0].'Pool'.self::$model_suffix;
				$this->write('if(substr($name,0,%s) == %s) {', strlen($field)+1, $field.'.');
				$this->write('return '.$subobjpool_class.'::map_name_to_backend(substr($name,%d),%s);', strlen($field)+1, $type[1]);
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
