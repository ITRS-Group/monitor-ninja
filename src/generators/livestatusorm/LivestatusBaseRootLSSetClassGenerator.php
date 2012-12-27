<?php

class LivestatusBaseRootLSSetClassGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->structure = $descr;
		$this->classname = "BaseObjectLSSet";
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( 'ObjectSet', array('abstract') );
		$this->generate_stats();
		$this->generate_count();
		$this->generate_it();
		$this->finish_class();
	}
	
	public function generate_stats() {
		$this->init_function('stats',array('intersections'));
		$this->write('$ls = LivestatusAccess::instance();');
		
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
	
	public function generate_count() {
		$this->init_function('count');
		$this->write('$ls = LivestatusAccess::instance();');

		$this->write('$ls_filter = $this->filter->visit(new LivestatusFilterBuilderVisitor(), false);');
		$this->write('$ls_filter .= "Limit: 0\n";');
		
		$this->write('$result = $ls->query($this->table, $ls_filter, false);');
		
		$this->write('return $result[2];');
		$this->finish_function();
	}
	
	public function generate_it() {
		$this->init_function( 'it', array('columns','order','limit','offset'), array(), array('limit'=>false, 'offset'=>false) );
		$this->write('$ls = LivestatusAccess::instance();');

		$this->write('$ls_filter = $this->filter->visit(new LivestatusFilterBuilderVisitor(), false);');
		
		$this->write('foreach($order as $col) {');
		$this->write('$ls_filter .= "Sort: $col\n";');
		$this->write('}');

		$this->write('if( $offset !== false ) {');
		$this->write('$ls_filter .= "Offset: ".intval($offset)."\n";');
		$this->write('}');
		
		$this->write('if( $limit !== false ) {');
		$this->write('$ls_filter .= "Limit: ".intval($limit)."\n";');
		$this->write('}');

		$this->write('if( $columns != false ) {');
		$this->write('$columns = $this->validate_columns($columns);');
		$this->write('if($columns === false) return false;');
		$this->write('}');
		
		$this->write('list($columns, $objects, $count) = $ls->query($this->table, $ls_filter, $columns);');
		$this->write('return new LivestatusSetIterator($objects, $columns, $this->class);');
		$this->finish_function();
	}
}