<?php

class ORMObjectSetGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	private $associations; /** an association is a way to get a one-to-many */
	private $relations; /** a relation is a way to declare a many-to-one for sql */

	public function __construct( $name, $structure ) {
		$this->name = $name;
		$this->structure = $structure[$name];
		$this->objectclass = $this->structure['class'].self::$model_suffix;
		$this->classname = 'Base'.$this->structure['class'].'Set';

		$this->associations = array();

		foreach( $structure as $table => $tbl_struct ) {
			foreach( $tbl_struct['structure'] as $name => $type ) {
				if( is_array( $type ) ) {
					if( $type[0] == $this->structure['class'] ) {
						$this->associations[] = array(
							$table,
							$tbl_struct['class'],
							substr( $type[1], 0, -1 ) // Drop last _
						);
					}
				}
			}
		}

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

		$this->set_model();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( 'Object'.$this->structure['source'].'Set', array('abstract') );
		if( isset($this->structure['db_instance']) ) {
			$this->variable('db_instance',$this->structure['db_instance'],'protected');
		}
		$this->variable('table',$this->name,'protected');

		$dbtable_expr = $dbtable = $this->name;

		if (isset($this->structure['table'])) {
			$dbtable = $this->structure['table'];
			$dbtable_expr = $this->structure['table'] . ' AS ' . $this->name;
		}
		if (isset($this->structure['relations'])) {
			$joinexpr = array();
			foreach ($this->structure['relations'] as $relation) {
				list($foreign_key, $table, $key) = $relation;
				$relations = $this->relations[$this->structure['structure'][$key][1]];
				$ons = array();
				for ($i = 0; $i < count($foreign_key); $i++) {
					$ons[] = "{$this->structure['structure'][$key][1]}.{$relations['tblkey'][$i]} = {$this->name}.{$foreign_key[$i]}";
				}
				$joinexpr[] = "LEFT JOIN {$relations["tbl"]} AS {$this->structure['structure'][$key][1]} ON " . implode(" AND ", $ons);
			}
			$dbtable_expr .= ' '.implode("", $joinexpr);
		}
		$this->variable('dbtable',$dbtable,'protected');
		$this->variable('dbtable_expr',$dbtable_expr,'protected');

		if( isset($this->structure['default_sort']) )
			$this->variable('default_sort',$this->structure['default_sort'],'protected');

		$this->variable('class',$this->structure['class'].self::$model_suffix,'protected');

		$this->generate_format_column_filter();
		$this->generate_format_column_selector();
		$this->generate_format_column_list();

		$this->generate_validate_columns();

		foreach( $this->associations as $assoc ) {
			$this->generate_association_get_set( $assoc[0], $assoc[1], $assoc[2] );
		}
		$this->finish_class();
	}

	public function generate_validate_columns() {
		$this->init_function('validate_columns', array('columns'));
		$this->write('$columns = parent::validate_columns($columns);');
		foreach($this->structure['structure'] as $name => $type ) {
			if( is_array($type) ) {
				$this->write('$subcolumns = array();');
				$this->write('$tmpcolumns = array();');
				$this->write('foreach( $columns as $col ) {');
				$this->write('if(substr($col,0,%s) == %s) {', strlen($name)+1,$name.'.');
				$this->write('$subcolumns[] = substr($col,%s);', strlen($name)+1);
				$this->write('} else {');
				$this->write('$tmpcolumns[] = $col;');
				$this->write('}');
				$this->write('}');
				$this->write('$columns = $tmpcolumns;');
				$this->write('$tmpset = '.$type[0].'Pool'.self::$model_suffix.'::all();');
				$this->write('$subcolumns = $tmpset->validate_columns($subcolumns);');
				$this->write('if($subcolumns === false) return false;');
				$this->write('foreach($subcolumns as $col) {');
				$this->write('$columns[] = %s.$col;', $name.'.');
				$this->write('}');
			}
		}
		foreach($this->structure['key'] as $keypart ) {
			$this->write('if( !in_array(%s, $columns) ) $columns[] = %s;', $keypart, $keypart);
		}
		$this->write('return $columns;');
		$this->finish_function();
	}

	public function generate_format_column_filter() {
		if (isset($this->structure['relations'])) {
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
	}

	/**
	 * Generate a function that returns a corrected column name
	 * for use in a SELECT clause for making proper aliases available
	 * to the ORM backend.
	 */
	public function generate_format_column_selector() {
		if (isset($this->structure['relations'])) {
			$this->init_function('format_column_selector', array('column'), array('private'));
			foreach ($this->structure['relations'] as $relation) {
				list($foreign_key, $table, $key) = $relation;
				$prefix = $this->structure['structure'][$key][1];
				$this->write('if (!strncmp("'.$prefix.'", $column, '.strlen($prefix).')) {');
				$this->write(    'return "'.$prefix.'.$column AS '.$prefix.'".substr($column, '.(strlen($prefix)+1).');');
				$this->write('}');
			}
			$this->write('return "'.$this->name.'.".$column;');
			$this->finish_function();
		}
	}

	public function generate_format_column_list() {
		if (isset($this->structure['relations'])) {
			$this->init_function('format_column_list', array('columns'), array('protected'), array('false'));
			$this->write('if ($columns == false) {');
			# This won't work quite right, as we won't get the prefix in place for foreign data. Meh.
			$this->write(    'return "'.$this->name.'.*, '.implode(', ', array_map(function($rel) { return $rel[2] . '.*'; }, $this->structure['relations'])).'";');
			$this->write('}');
			$this->write('return implode(", ", array_map(array($this, "format_column_selector"), $columns));');
			$this->finish_function();
		}
	}

	private function generate_association_get_set($table, $class, $field) {
		$this->init_function('get_'.$table);
		$this->write('$result = '.$class.'Pool'.self::$model_suffix.'::all();');
		$this->write('$result->filter = $this->filter->prefix(%s);', $field.'.');
		$this->write('return $result;');
		$this->finish_function();
	}
}
