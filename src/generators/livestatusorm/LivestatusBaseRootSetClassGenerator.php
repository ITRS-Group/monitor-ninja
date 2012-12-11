<?php

class LivestatusBaseRootSetClassGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->structure = $descr;
		$this->classname = "BaseObjectSet";
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( false, array('abstract'), array("IteratorAggregate", "Countable") );
		$this->generate_construct();
		$this->variable('table',null,'protected');
		$this->variable('class',null,'protected');
		$this->variable('filter',null,'protected');
		$this->generate_getter('table');
		$this->generate_getter('class');
		$this->generate_binary_operator('union', 'LivestatusFilterOr');
		$this->generate_binary_operator('intersect', 'LivestatusFilterAnd');
		$this->generate_unary_operator('complement','LivestatusFilterNot');
		$this->generate_reduce();
		$this->generate_convert_to_object();
		$this->generate_stats();
		$this->generate_count();
		$this->generate_getIterator();
		$this->generate_it();
		$this->finish_class();
	}
	
	public function generate_construct() {
		$this->init_function('__construct');
		$this->write('$this->filter = new LivestatusFilterAnd();');
		$this->finish_function();
	}
	
	public function generate_getter($var) {
		$this->init_function('get_'.$var);
		$this->write('return $this->'.$var.';');
		$this->finish_function();
	}
	
	public function generate_unary_operator($operator,$filterclass) {
		$this->init_function($operator);
		
		$this->write('$filter = new '.$filterclass.'($this->filter);');
		
		$this->write('$result = new static();');
		$this->write('$result->filter = $filter;');
		$this->write('return $result;');
		$this->finish_function();
	}
	
	public function generate_binary_operator($operator,$filterclass) {
		$this->init_function($operator, array('set'));
		$this->write('if( $this->table != $set->table ) {');
		$this->write('return false;');
		$this->write('}');
		
		$this->write('$filter = new '.$filterclass.'();');
		$this->write('$filter->add( $this->filter );');
		$this->write('$filter->add( $set->filter );');
		
		$this->write('$result = new static();');
		$this->write('$result->filter = $filter;');
		$this->write('return $result;');
		$this->finish_function();
	}
	
	public function generate_reduce() {
		$this->init_function('reduceBy', array('column', 'value', 'op'));
		$this->write('$filter = new LivestatusFilterAnd();');
		$this->write('$filter->add( $this->filter );');
		$this->write('$filter->add( new LivestatusFilterMatch( $column, $value, $op ) );');
		
		$this->write('$result = new static();');
		$this->write('$result->filter = $filter;');
		$this->write('return $result;');
		$this->finish_function();
	}
	
	public function generate_convert_to_object() {
		$this->init_function('convert_to_object', array('table','field'));
		$this->write('$result = ObjectPool'.self::$model_suffix.'::pool($table)->all();');
		$this->write('$result->filter = $this->filter->prefix($field . "_");');
		$this->write('return $result;');
		$this->finish_function();
	}
	
	public function generate_stats() {
		$this->init_function('stats',array('intersections'));
		$this->write('$ls = LivestatusAccess::instance();');
		
		$this->write('$single = !is_array($intersections);');
		$this->write('if($single) $intersections = array($intersections);');
		
		$this->write('$ls_filter = $this->filter->generateFilter();');
		
		$this->write('$ls_intersections = array();');
		$this->write('foreach( $intersections as $name => $intersection ) {');
		$this->write('if($intersection->table == $this->table) {');
		$this->write('$ls_intersections[$name] = $intersection->filter->generateStats();');
		$this->write('}'); // TODO: Error handling...
		$this->write('}');
		
		$this->write('$result = $ls->stats_single($this->table, $ls_filter, $ls_intersections);');
		
		$this->write('if($single) $result = $result[0];');
		$this->write('return $result;');
		$this->finish_function();
	}
	
	public function generate_count() {
		$this->init_function('count');
		$this->write('$ls = LivestatusAccess::instance();');

		$this->write('$ls_filter = $this->filter->generateFilter();');
		$this->write('$ls_filter .= "Limit: 0\n";');
		
		$this->write('$result = $ls->query($this->table, $ls_filter, false);');
		
		$this->write('return $result[2];');
		$this->finish_function();
	}
	
	public function generate_getIterator() {
		$this->init_function('getIterator');
		$this->write('return $this->it(false,array());');
		$this->finish_function();
	}
	
	public function generate_it() {
		$this->init_function( 'it', array('columns','order','limit','offset'), array(), array('limit'=>false, 'offset'=>false) );
		$this->write('$ls = LivestatusAccess::instance();');

		$this->write('$ls_filter = $this->filter->generateFilter();');
		
		$this->write('foreach($order as $col) {');
		$this->write('$ls_filter .= "Sort: $col\n";');
		$this->write('}');

		$this->write('if( $offset !== false ) {');
		$this->write('$ls_filter .= "Offset: ".intval($offset)."\n";');
		$this->write('}');
		
		$this->write('if( $limit !== false ) {');
		$this->write('$ls_filter .= "Limit: ".intval($limit)."\n";');
		$this->write('}');
		
		$this->write('$columns = $this->validate_columns($columns);');
		$this->write('if($columns === false) return false;');
		
		$this->write('list($columns, $objects, $count) = $ls->query($this->table, $ls_filter, $columns);');
		$this->write('return new LivestatusSetIterator($objects, $columns, $this->class);');
		$this->finish_function();
	}
}