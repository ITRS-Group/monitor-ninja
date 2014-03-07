<?php

abstract class ORMObjectSetGenerator extends class_generator {
	public $name;
	public $structure;
	public $objectclass;
	public $associations; /** an association is a way to get a one-to-many */

	public function __construct( $name, $structure ) {
		$this->name = $name;
		$this->structure = $structure[$name];
		$this->objectclass = $this->structure['class'].self::$model_suffix;
		$this->classname = 'Base'.$this->structure['class'].'Set';
		parent::generate();

		$this->associations = array();

		foreach( $structure as $table => $tbl_struct ) {
			foreach( $tbl_struct['structure'] as $name => $type ) {
				if( is_array( $type ) ) {
					if( $type[0] == $this->structure['class'] ) {
						$this->associations[] = array(
							$table,
							$tbl_struct['class'],
							$name
						);
					}
				}
			}
		}

		$this->set_model();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( 'ObjectSet', array('abstract') );
		$this->variable('table',$this->name,'protected');

		if( isset($this->structure['default_sort']) )
			$this->variable('default_sort',$this->structure['default_sort'],'protected');

		$this->variable('class',$this->structure['class'].self::$model_suffix,'protected');
		$this->variable('key_columns',$this->structure['key'],'protected');

		$this->generate_backend_specific_functions();

		$this->generate_apply_columns_rewrite();
		$this->generate_filter_valid_columns();
		$this->generate_get_all_columns_list();

		/* External interface, backend specific */
		$this->generate_stats();
		$this->generate_count();
		$this->generate_it();

		/* Interface used by orm-related libraries (some visitors) */
		$this->generate_process_field_name();

		foreach( $this->associations as $assoc ) {
			$this->generate_association_get_set( $assoc[0], $assoc[1], $assoc[2] );
		}
		$this->finish_class();
	}

	public function generate_apply_columns_rewrite() {
		$this->init_function('apply_columns_rewrite', array('columns', 'prefix'),array('static'),array('prefix'=>''));
		$this->write( 'foreach('.$this->structure['class'].self::$model_suffix.'::$rewrite_columns as $column => $rewrites) {');
		$this->write(   'if( in_array( $prefix.$column, $columns ) ) {' );
		$this->write(     'foreach($rewrites as $rewrite) {' );
		$this->write(       '$columns[] = $prefix.$rewrite;' );
		$this->write(     '}' );
		$this->write(   '}' );
		$this->write( '}' );
		foreach( $this->structure['structure'] as $name => $type ) {
			if(isset($this->structure['rename']) && isset($this->structure['rename'][$name])) {
				$name = $this->structure['rename'][$name];
			}
			if(is_array($type)) {
				$this->write('$columns = '.$type[0].'Set'.self::$model_suffix.'::apply_columns_rewrite($columns,%s);',$name.".");
			}
		}
		$this->write('return $columns;');
		$this->finish_function();
	}

	public function generate_filter_valid_columns() {
		$translated_structure = array();
		foreach( $this->structure['structure'] as $name => $type ) {
			if(isset($this->structure['rename']) && isset($this->structure['rename'][$name])) {
				$name = $this->structure['rename'][$name];
			}
			$translated_structure[$name] = $type;
		}

		$this->init_function('filter_valid_columns', array('columns','prefix'), array('static'), array('prefix'=>''));
		$this->write('$in_columns = array_flip($columns);');
		$this->write('$out_columns = array();');

		foreach($translated_structure as $name => $type ) {
			if( !is_array($type) ) {
				$this->write('if(isset($in_columns[$prefix.%s])) {', $name);
				$this->write('$out_columns[] = $prefix.%s;',$name);
				$this->write('}');
			}
		}
		foreach($translated_structure as $name => $type ) {
			if( is_array($type) ) {
				$this->write('$tmpset = '.$type[0].'Pool'.self::$model_suffix.'::all();');
				$this->write('$sub_columns = $tmpset->filter_valid_columns($columns,%s);',$type[1]);
				$this->write('$out_columns = array_merge($out_columns, $sub_columns);');
			}
		}
		$this->write('return $out_columns;');
		$this->finish_function();
	}

	public function generate_get_all_columns_list() {
		$columns = array();
		$subobjs = array();
		foreach ($this->structure['structure'] as $name => $type) {
			if (is_array($type)) {
				$subobjs[$name] = $type;
			} else {
				$columns[] = $name;
			}
		}
		$this->init_function('get_all_columns_list', array('include_nested'), array('static'), array('include_nested'=>true));
		$this->write('$raw_columns = %s;', $columns);
		$this->write('$sub_columns = array();');
		$this->write('if ($include_nested) {');
		foreach ($subobjs as $name => $type) {
			$this->write('$obj_cols = '.$type[0].'Set'.self::$model_suffix.'::get_all_columns_list(false);');
			$this->write('foreach ($obj_cols as $name) {');
			$this->write('$sub_columns[] = %s.$name;', $name.'.');
			$this->write('}');
		}
		$this->write('}');
		$this->write('$virtual_columns = array_keys('.$this->objectclass.'::$rewrite_columns);');
		$this->write('return array_merge($sub_columns, $raw_columns, $virtual_columns);');
		$this->finish_function();
	}

	public function generate_association_get_set($table, $class, $field) {
		$this->init_function('get_'.$table);
		$this->write('$result = '.$class.'Pool'.self::$model_suffix.'::all();');
		$this->write('$result->filter = $this->filter->prefix(%s);', $field.'.');
		$this->write('return $result;');
		$this->finish_function();
	}

	abstract public function generate_it();
	abstract public function generate_count();
	abstract public function generate_stats();
	abstract public function generate_process_field_name();
	abstract public function generate_backend_specific_functions();
}
