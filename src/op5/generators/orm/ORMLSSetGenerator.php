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

		$this->write('$ls_filter = $this->filter->visit(new LivestatusFilterBuilderVisitor(), false);');

		$this->write('$ls_intersections = array();');
		$this->write('foreach( $intersections as $name => $intersection ) {');
		$this->write('if($intersection->table == $this->table) {');
		$this->write('$ls_intersections[$name] = $intersection->filter->visit(new LivestatusStatsBuilderVisitor(), false);');
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
		$this->write('$ls_filter = $filter->visit(new LivestatusFilterBuilderVisitor(), false);');
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
		$this->write('$ls_filter = $filter->visit(new LivestatusFilterBuilderVisitor(), false);');

		$this->write('foreach($order as $col) {');
		$this->write('$ls_filter .= "Sort: $col\n";');
		$this->write('}');

		$this->write('foreach($this->default_sort as $col) {');
		$this->write('$ls_filter .= "Sort: $col\n";');
		$this->write('}');

		$this->write('if( $offset !== false ) {');
		$this->write('$ls_filter .= "Offset: ".intval($offset)."\n";');
		$this->write('}');

		$this->write('if( $limit !== false ) {');
		$this->write('$ls_filter .= "Limit: ".intval($limit)."\n";');
		$this->write('}');

		$this->write('if( $columns !== false ) {');
		$this->write('$columns = $this->validate_columns($columns);');
		$this->write('if($columns === false) return false;');
		$this->write('$columns = array_unique($columns);');
		$this->write('}');

		$this->write('try {');
		$this->write('list($columns, $objects, $count) = $ls->query($this->table, $ls_filter, $columns);');
		$this->write('} catch( op5LivestatusException $e ) {');
		$this->write('throw new ORMException( $e->getPlainMessage() );');
		$this->write('}');
		$this->write('return new LivestatusSetIterator($objects, $columns, $this->class);');
		$this->finish_function();
	}
}
