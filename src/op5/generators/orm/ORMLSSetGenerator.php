<?php

class ORMLSSetGenerator extends class_generator {
	private $name;
	private $objectclass;

	/**
	 * Construct
	 *
	 * @return void
	 **/
	public function __construct( $name ) {
		$this->name = $name;
		$this->classname = "BaseObjectLSSet";
		$this->set_model();
	}

	/**
	 * Generate
	 *
	 * @param $skip_generate_note boolean
	 * @return void
	 **/
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->classfile("op5/livestatus.php");
		$this->init_class( 'ObjectSet', array('abstract') );
		$this->generate_stats();
		$this->generate_count();
		$this->generate_it();
		$this->generate_process_field_name();
		$this->finish_class();
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

		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor($this);');
		$this->write('$sb_visit = new LivestatusStatsBuilderVisitor($this);');
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
		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor($this);');
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
		$this->write('$fb_visit = new LivestatusFilterBuilderVisitor($this);');
		$this->write('$ls_filter = $filter->visit($fb_visit, false);');

		foreach(array('$order','$this->default_sort') as $sortfield) {
			$this->write('foreach('.$sortfield.' as $col_attr) {');
			$this->write('$parts = explode(" ",$col_attr);');
			$this->write('$parts[0] = static::process_field_name($parts[0]);');
			$this->write('$parts = array_filter($parts);');
			$this->write('$ls_filter .= "Sort: ".implode(" ",$parts)."\n";');
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
		$this->write(  '$tmp_columns = array();');
		$this->write(  'foreach($processed_columns as $col) {');
		$this->write(    '$tmp_columns[] = static::process_field_name($col);');
		$this->write(  '}');
		$this->write(  '$valid_columns = static::filter_valid_columns($tmp_columns);');
		$this->write(  'if($valid_columns === false) return false;');
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

	private function generate_process_field_name() {
		$this->init_function('process_field_name', array('name'), array('static'));
		$this->write('return str_replace(".","_",parent::process_field_name($name));');
		$this->write('}');
	}
}
