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
		$this->variable('db_instance','default','protected');
		$this->generate_format_column_filter();
		$this->generate_format_column_list();
		$this->generate_stats();
		$this->generate_count();
		$this->generate_it();
		$this->generate_process_field_name();
		$this->finish_class();
	}

	public function generate_format_column_filter() {
		$this->init_function('format_column_filter', array('column'));
		$this->write('return $this->table.".".$column;');
		$this->finish_function();
	}

	public function generate_format_column_list() {
		$this->init_function('format_column_list', array('columns'), array('protected'), array('false'));
		$this->write('if ($columns == false) {');
		$this->write(    'return "*";');
		$this->write('}');
		$this->write('return implode(", ", $columns);');
		$this->finish_function();
	}

	public function generate_stats() {
		$this->init_function('stats',array('intersections'));
		$this->write('return array();');
		$this->finish_function();
	}

	public function generate_count() {
		$this->init_function('count');
		$this->write('$db = Database::instance($this->db_instance);');
		$this->write('$filter = $this->get_auth_filter();');
		$this->write('$sql = "SELECT COUNT(*) AS count FROM ".$this->dbtable_expr;');
		$this->write('$sql .= " WHERE ".$filter->visit(new LivestatusSQLBuilderVisitor(array($this, "format_column_filter")), false);');
		$this->write('$q = $db->query($sql);');
		$this->write('$q->result(false);');
		$this->write('$row = $q->current();');
		$this->write('return $row["count"];');
		$this->finish_function();
	}

	public function generate_it() {
		$this->init_function( 'it', array('columns','order','limit','offset'), array(), array('order' => array(), 'limit'=>false, 'offset'=>false) );
		$this->write('$db = Database::instance($this->db_instance);');

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

		$this->write('$filter = $this->get_auth_filter();');

		$this->write('$sql = "SELECT ".$this->format_column_list($valid_columns)." FROM ".$this->dbtable_expr;');
		$this->write('$sql .= " WHERE ".$filter->visit(new LivestatusSQLBuilderVisitor(array($this, "format_column_filter")), false);');

		$this->write('$sort = array();');
		foreach(array('$order','$this->default_sort') as $sortfield) {
			$this->write('foreach('.$sortfield.' as $col_attr) {');
				$this->write('$parts = explode(" ",$col_attr,2);');
				$this->write('$parts[0] = static::process_field_name($parts[0]);');
				$this->write('if(!preg_match("/^[a-zA-Z0-9_.]+$/",$parts[0])) continue;');
				$this->write('if(isset($parts[1]) && !preg_match("/^(asc|desc)$/i",$parts[1])) continue;');
				$this->write('$parts = array_filter($parts);');
				$this->write('$sort[] = implode(" ",$parts);');
			$this->write('}');
		}
		$this->write('if(!empty($sort)) {');
		$this->write('$sql .= " ORDER BY ".implode(", ",$sort);');
		$this->write('}');

		$this->write('if( $limit !== false ) {');
		$this->write(    '$sql .= " LIMIT ";');
		$this->write(    'if( $offset !== false ) {');
		$this->write(        '$sql .= intval($offset) . ", ";');
		$this->write(    '}');
		$this->write(    '$sql .= intval($limit);');
		$this->write('}');

		$this->write('$q = $db->query($sql);');
		$this->write('$q->result(false, MYSQL_NUM);');

		$this->write('$fetched_columns = $q->list_fields();');

		$this->write('if($columns === false) {');
		$this->write(    '$columns = static::get_all_columns_list();');
		$this->write('}');

		$this->write('return new LivestatusSetIterator($q, $fetched_columns, $columns, $this->class);');
		$this->finish_function();
	}

	private function generate_process_field_name() {
		$this->init_function('process_field_name', array('name'), array('static'));
		$this->write('return preg_replace("/[^a-zA-Z._]/","",$name);');
		$this->write('}');
	}
}
