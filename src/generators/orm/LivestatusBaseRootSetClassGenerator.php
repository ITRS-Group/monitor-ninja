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
		$this->variable('dbtable',null,'protected');
		$this->variable('class',null,'protected');
		$this->variable('filter',null,'protected');
		$this->variable('default_sort',null,'protected');
		$this->generate_getter('table');
		$this->generate_getter('class');
		$this->generate_binary_operator('union', 'LivestatusFilterOr');
		$this->generate_binary_operator('intersect', 'LivestatusFilterAnd');
		$this->generate_unary_operator('complement','LivestatusFilterNot');
		$this->generate_reduce();
		$this->generate_convert_to_object();
		$this->generate_stats();
		$this->generate_getIterator();
		$this->generate_it();
		$this->generate_get_auth_filter();
		$this->generate_validate_columns();
		$this->finish_class();
	}
	
	public function generate_construct() {
		$this->init_function('__construct', array('filter'));
		$this->write('$this->filter = $filter;');
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

		$this->write('return new static($filter);');
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
		
		$this->write('return new static($filter);');
		$this->finish_function();
	}
	
	public function generate_reduce() {
		$this->init_function('reduce_by', array('column', 'value', 'op'));
		$this->write('$filter = new LivestatusFilterAnd();');
		$this->write('$filter->add( $this->filter );');
		$this->write('$filter->add( new LivestatusFilterMatch( $column, $value, $op ) );');

		$this->write('return new static($filter);');
		$this->finish_function();
	}
	
	public function generate_convert_to_object() {
		$this->init_function('convert_to_object', array('table','field'));
		$this->write('$result = ObjectPool'.self::$model_suffix.'::pool($table)->all();');
		$this->write('$result->filter = $this->filter->prefix($field . ".");');
		$this->write('return $result;');
		$this->finish_function();
	}
	
	public function generate_stats() {
		$this->abstract_function('stats',array('intersections'));
	}
	
	public function generate_getIterator() {
		$this->init_function('getIterator');
		$this->write('return $this->it(false,array());');
		$this->finish_function();
	}
	
	public function generate_it() {
		$this->abstract_function( 'it', array('columns','order','limit','offset'), array(), array('limit'=>false, 'offset'=>false) );
	}
	
	public function generate_get_auth_filter() {
		$this->init_function('get_auth_filter',array(),array('protected'));
		$this->write('return $this->filter;');
		$this->finish_function();
	}

	public function generate_validate_columns() {
		$this->init_function('validate_columns', array('columns'));
		$this->write( '$classname = $this->class;' );
		$this->write( '$prefix = "";' );
		$this->write( 'foreach($classname::$rewrite_columns as $column => $rewrites) {');
		$this->write(   'if( in_array( $column, $columns ) ) {' );
		$this->write(     '$columns = array_filter( $columns, function($row) use($column) {return $row != $column;} );' );
		$this->write(     'foreach($rewrites as $rewrite) {' );
		$this->write(       '$columns[] = $rewrite;' );
		$this->write(     '}' );
		$this->write(   '}' );
		$this->write( '}' );
		$this->write('return $columns;');
		$this->finish_function();
	}
}