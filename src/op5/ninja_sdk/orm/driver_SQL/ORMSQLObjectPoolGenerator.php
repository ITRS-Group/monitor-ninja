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
					'tbl' => $full_structure[$table]['table'],
					'tblkey' => $full_structure[$table]['key'],
				);
			}
		} else {
			$this->structure['relations'] = array();
		}
	}

	public function generate_backend_specific_functions() {
		$this->generate_map_name_to_backend();
	}

	private function build_sql_from_where() {
		$table = $this->structure['table'];
		$this->write('$sql .= %s;', ' FROM ' . $table);
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

	private function build_sql_where() {
		/* We assume a single table */
		$this->write('$sql .= " WHERE ".$filter->visit(new '.$this->visitor_class.'(array(%s, "map_name_to_backend")), false);', $this->structure['class'].'Pool'.self::$model_suffix);
	}

	/**
	 * Generate the method map_name_to_backend for the object set
	 *
	 * @param $oset ORMObjectSetGenerator
	 */
	public function generate_map_name_to_backend() {
		$this->init_function('map_name_to_backend', array('name', 'prefix'), array('static'), array('prefix' => false));
		$this->write('if($prefix === false) {');
		$this->write('$prefix = %s;', $this->structure['table'].'.');
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
