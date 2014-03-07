<?php

require_once("ORMObjectSetGenerator.php");

class ORMSQLSetGenerator extends ORMObjectSetGenerator {
	public $relations; /** a relation is a way to declare a many-to-one for sql */

	public function __construct( $name, $structure ) {
		parent::__construct($name, $structure);

		if (isset($this->structure['relations'])) {
			foreach ($this->structure['relations'] as $relation) {
				list($foreign_key, $table, $key) = $relation;
				$this->relations[$this->structure['structure'][$key][1]] = array(
					'tbl' => $structure[$table]['table'],
					'tblkey' => $structure[$table]['key'],
				);
			}
		}
		else {
			$this->relations = array();
		}
	}

	public function generate_backend_specific_functions() {
		$db_instance = false;
		if( isset($this->structure['db_instance']) ) {
			$db_instance = $this->structure['db_instance'];
		}
		$this->variable('db_instance',$db_instance,'protected');

		if(!isset($this->structure['relations'])) {
			$this->structure['relations'] = array();
		}

		$dbtable_expr = $dbtable = $this->name;

		$dbtable = $this->structure['table'];
		$dbtable_expr = $this->structure['table'] . ' AS ' . $this->name;

		$joinexpr = array();
		foreach ($this->structure['relations'] as $relation) {
			list($foreign_key, $table, $key) = $relation;
			$virtual_table_name = rtrim($this->structure['structure'][$key][1],'.');
			$relations = $this->relations[$this->structure['structure'][$key][1]];
			$ons = array();
			for ($i = 0; $i < count($foreign_key); $i++) {
				$ons[] = "$virtual_table_name.{$relations['tblkey'][$i]} = {$this->name}.{$foreign_key[$i]}";
			}
			$joinexpr[] = "LEFT JOIN {$relations["tbl"]} AS $virtual_table_name ON " . implode(" AND ", $ons);
		}
		$dbtable_expr .= ' '.implode("", $joinexpr);

		$this->variable('dbtable',$dbtable,'protected');
		$this->variable('dbtable_expr',$dbtable_expr,'protected');


		$this->generate_format_column_filter();
		$this->generate_format_column_selector();
		$this->generate_format_column_list();
	}

	public function generate_format_column_filter() {
		$this->init_function('format_column_filter', array('column'));
		foreach ($this->structure['relations'] as $relation) {
			list($foreign_key, $table, $key) = $relation;
			$prefix = $this->structure['structure'][$key][1];
			$this->write('if (!strncmp("'.$prefix.'", $column, '.strlen($prefix).')) {');
			$this->write(    'return "'.$prefix.'.".substr($column, '.(strlen($prefix)+1).');');
			$this->write('}');
		}
		$this->write('return "'.$this->name.'.".$column;');
		$this->finish_function();
	}

	/**
	 * Generate a function that returns a corrected column name
	 * for use in a SELECT clause for making proper aliases available
	 * to the ORM backend.
	 */
	public function generate_format_column_selector() {
		$this->init_function('format_column_selector', array('column'), array('private'));
		foreach ($this->structure['relations'] as $relation) {
			list($foreign_key, $table, $key) = $relation;
			$prefix = $this->structure['structure'][$key][1];
			$virtual_table_name = rtrim($prefix,'.');
			$this->write('if (!strncmp("'.$prefix.'", $column, %s)) {',strlen($prefix));
			$this->write(    'return "'.$virtual_table_name.'.$column AS '.$virtual_table_name.'_".substr($column, %s);',strlen($prefix));
			$this->write('}');
		}
		$this->write('return "'.$this->name.'.".$column;');
		$this->finish_function();
	}

	public function generate_format_column_list() {
		$table_names = array($this->name);
		foreach( $this->structure['relations'] as $rel ) {
			$table_names[] = $rel[2];
		}

		$this->init_function('format_column_list', array('columns'), array('protected'), array('false'));
		$this->write('if ($columns == false) {');
		# This won't work quite right, as we won't get the prefix in place for foreign data. Meh.
		$this->write(    'return %s;', implode(', ', array_map(function($rel) { return $rel . '.*'; }, $table_names)));
		$this->write('}');
		$this->write('return implode(", ", array_map(array($this, "format_column_selector"), $columns));');
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

	/**
	 * Generate the method process_field_name for the object set
	 *
	 * @param $oset ORMObjectSetGenerator
	 */
	public function generate_process_field_name() {
		$this->init_function('process_field_name', array('name'), array('static'));
		if(isset($this->structure['rename'])) {
			foreach($this->structure['rename'] as $source => $dest ) {
				$this->write('if($name == %s) {', $source);
				$this->write('$name = %s;', $dest);
				$this->write('}');
			}
		}
		foreach($this->structure['structure'] as $field => $type ) {
			if(is_array($type)) {
				$subobjset_class = $type[0].'Set'.self::$model_suffix;
				$this->write('if(substr($name,0,%s) == %s) {', strlen($field)+1, $field.'.');
				$this->write('$subobj_name = substr($name,%d);', strlen($field)+1);
				// Somewhat a livestatus hack, but probably sql to. Only keep the innermost object name if contianing objects
				$this->write('$prefix = "";');
				$this->write('if(false===strpos($subobj_name,".")) {');
				$this->write('$prefix = %s;', $field.'.');
				$this->write('}');
				$this->write('$name = $prefix.'.$subobjset_class.'::process_field_name($subobj_name);');
				$this->write('}');
			}
		}
		$this->write('return preg_replace("/[^a-zA-Z._]/","",$name);');
		$this->write('}');
	}
}
