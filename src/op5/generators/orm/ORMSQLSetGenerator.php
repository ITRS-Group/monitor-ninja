<?php

class ORMSQLSetGenerator extends class_generator {
	private $name;
	private $objectclass;

	public function __construct( $name ) {
		$this->name = $name;
		$this->classname = "BaseObjectSQLSet";
		$this->set_model();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( 'ObjectSet', array('abstract') );
		$this->generate_stats();
		$this->generate_count();
		$this->generate_it();
		$this->finish_class();
	}

	public function generate_stats() {
		$this->init_function('stats',array('intersections'));
		$this->write('return array();');
		$this->finish_function();
	}

	public function generate_count() {
		$this->init_function('count');
		$this->write('$db = Database::instance();');
		$this->write('$filter = $this->get_auth_filter();');
		$this->write('$sql = "SELECT COUNT(*) AS count FROM ".$this->dbtable;');
		$this->write('$sql .= " WHERE ".$filter->visit(new LivestatusSQLBuilderVisitor(), false);');
		$this->write('$q = $db->query($sql);');
		$this->write('$q->result(false);');
		$this->write('$row = $q->current();');
		$this->write('return $row["count"];');
		$this->finish_function();
	}

	public function generate_it() {
		$this->init_function( 'it', array('columns','order','limit','offset'), array(), array('limit'=>false, 'offset'=>false) );
		$this->write('$db = Database::instance();');
		
		$this->write('if( $columns != false ) {');
		$this->write('$columns = $this->validate_columns($columns);');
		$this->write('if($columns === false) return false;');
		$this->write('$columns = array_unique($columns);');
		$this->write('}');
		
		$this->write('$filter = $this->get_auth_filter();');
		
		$this->write('if($columns === false) {');
		$this->write(    '$sql = "SELECT * FROM ".$this->dbtable;');
		$this->write('} else {');	
		$this->write(    '$sql = "SELECT ".str_replace(".","_",implode(",",$columns))." FROM ".$this->dbtable;');
		$this->write('}');
		$this->write('$sql .= " WHERE ".$filter->visit(new LivestatusSQLBuilderVisitor(), false);');
		
		$this->write('$sort = array();');
		$this->write('foreach($order as $col) {');
		$this->write('$sort[] = $col;');
		$this->write('}');
		$this->write('foreach($this->default_sort as $col) {');
		$this->write('$sort[] = $col;');
		$this->write('}');
		$this->write('$sql .= " ORDER BY ".implode(", ",$sort);');
		
		$this->write('if( $limit !== false ) {');
		$this->write(    '$sql .= " LIMIT ";');
		$this->write(    'if( $offset !== false ) {');
		$this->write(        '$sql .= intval($offset) . ", ";');
		$this->write(    '}');
		$this->write(    '$sql .= intval($limit);');
		$this->write('}');
		
		$this->write('$q = $db->query($sql);');
		$this->write('$q->result(false);');
		$this->write('return new LivestatusSetIterator($q, $q->list_fields(), $this->class);');
		$this->finish_function();
	}
}
