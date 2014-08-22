<?php

require_once(__DIR__."/../common/ORMObjectPoolGenerator.php");

abstract class ORMSQLObjectPoolGenerator extends ORMObjectPoolGenerator {
	public $relations; /** a relation is a way to declare a many-to-one for sql */
	public $db_instance;

	protected $visitor_class = "LivestatusSQLBuilderVisitor";

	public function __construct( $name, $full_structure ) {
		parent::__construct($name, $full_structure);

		$this->relations = array();

		if (isset($this->structure['relations'])) {
			foreach ($this->structure['relations'] as $relation) {
				list($foreign_key, $table, $key) = $relation;
				$this->relations[$this->structure['structure'][$key][1]] = array(
					'tbl' => $structure[$table]['table'],
					'tblkey' => $structure[$table]['key'],
				);
			}
		} else {
			$this->structure['relations'] = array();
		}

		$this->db_instance = false;
		if( isset($this->structure['db_instance']) ) {
			$this->db_instance = $this->structure['db_instance'];
		}
	}

	public function generate_backend_specific_functions() {
		$this->generate_map_name_to_backend();
	}

	public function generate_stats() {
		$this->init_function('stats',array('intersections'));
		$this->write('return array();');
		$this->finish_function();
	}

	private function build_sql_from_where() {
		$table = $this->name;
		$this->write('$sql .= %s;', ' FROM ' . $this->structure['table'] . ' AS ' . $this->name);
		foreach ($this->structure['relations'] as $relation) {
			list($foreign_key, $foreign_table, $key) = $relation;
			$foreign_structure = $this->full_structure[$foreign_table];
			$ftable = $foreign_structure['table'];
			$join_expr = ' LEFT JOIN '.$ftable.' AS '.$key;
			$join_expr .= ' ON '.implode(' AND ',array_map(function($fk,$lk) use($key,$table) {
				return "$key.$fk = $table.$lk";
			}, $foreign_structure['key'], $foreign_key));
			$this->write('$sql .= %s;', $join_expr);
		}
		$this->write('$sql .= " WHERE ".$filter->visit(new '.$this->visitor_class.'(array(%s, "map_name_to_backend")), false);', $this->structure['class'].'Pool'.self::$model_suffix);
	}

	public function generate_count() {
		$this->init_function('count', array('filter'));
		$this->write('$db = Database::instance(%s);',$this->db_instance);
		$this->write('$sql = "SELECT COUNT(*) AS count";');
		$this->build_sql_from_where();
		$this->write('$q = $db->query($sql);');
		$this->write('$q->result(false);');
		$this->write('$row = $q->current();');
		$this->write('return $row["count"];');
		$this->finish_function();
	}

	public function generate_it() {
		$table = $this->name;
		$this->init_function( 'it', array('filter','columns','order','limit','offset'), array('static'), array('order' => array(), 'limit'=>false, 'offset'=>false) );
		$this->write('$db = Database::instance(%s);',$this->db_instance);

		$this->write('$valid_columns = false;');
		$this->write('if( $columns !== false ) {');
		$this->write(  '$processed_columns = array_merge($columns, %s);', $this->structure['key']);
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

		$this->write('$sql = "SELECT ";');
		$this->write('if ($valid_columns === false) {');
		$table_names = array($this->name);
		foreach( $this->structure['relations'] as $rel ) {
			$table_names[] = $rel[2];
		}
		$this->write(  '$sql .= %s;', implode(', ', array_map(function($rel) { return $rel . '.*'; }, $table_names)));
		$this->write('} else {');
		$this->write(  '$sql .= implode(", ", $valid_columns);');
		$this->write('}');
		$this->build_sql_from_where();

		$this->write('$sort = array();');
		foreach(array('$order','static::$default_sort') as $sortfield) {
			$this->write('foreach('.$sortfield.' as $col_attr) {');
			$this->write(  '$parts = explode(" ",$col_attr,2);');
			$this->write(  'if(isset($parts[1]) && !preg_match("/^(asc|desc)$/i",$parts[1])) continue;');
			$this->write(  '$original_part_0 = $parts[0];');
			$this->write(  '$parts[0] = static::map_name_to_backend($parts[0]);');
			$this->write(  'if($parts[0] === false) {');
			$this->write(    'throw new ORMException(%s.$original_part_0."\'");', "Table '".$this->name."' has no column '");
			$this->write(  '}');
			$this->write(  '$sort[] = implode(" ",$parts);');
			$this->write('}');
		}
		$this->write('if(!empty($sort)) {');
		$this->write('$sql .= " ORDER BY ".implode(", ",$sort);');
		$this->write('}');

		$this->write('if( $limit !== false ) {');
		$this->write(    '$sql .= " LIMIT ";');
		$this->write(    '$sql .= intval($limit);');
		$this->write(    'if( $offset !== false ) {');
		$this->write(        '$sql .= " OFFSET " . intval($offset);');
		$this->write(    '}');
		$this->write('}');

		$this->write('$q = $db->query($sql);');
		$this->write('$q->result(false, MYSQL_NUM);');

		$this->write('$fetched_columns_raw = $q->list_fields(true);');

		$this->write('$fetched_columns = array();');
		$this->write('foreach($fetched_columns_raw as $col) {');
		$this->write('if(substr($col,0,%s) == %s) {', strlen($this->name)+1, $this->name.'.');
		$this->write('$fetched_columns[] = substr($col,%s);', strlen($this->name)+1);
		$this->write('} else {');
		$this->write('$fetched_columns[] = $col;');
		$this->write('}');
		$this->write('}');

		$this->write('if($columns === false) {');
		$this->write(    '$columns = static::get_all_columns_list();');
		$this->write('}');

		$this->write('return new LivestatusSetIterator($q, $fetched_columns, $columns, %s);', $this->obj_class);
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
		$this->write('$prefix = %s;', $this->name.'.');
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
