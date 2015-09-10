<?php

require_once('ORMGenerator.php');

abstract class ORMObjectGenerator extends ORMGenerator {
	protected $associations;

	/**
	 * Construct
	 *
	 * @return void
	 **/
	public function __construct( $name, $full_structure ) {
		parent::__construct($name, $full_structure);
		$this->classname = 'Base'.$this->structure['class'];

		$this->associations = array();

		foreach( $this->full_structure as $table => $tbl_struct ) {
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

		$this->parent_class = 'Object';
		if(isset($this->structure['object_custom_parent'])) {
			$this->parent_class = $this->structure['object_custom_parent'];
		}
	}

	/**
	 * Generate
	 *
	 * @return void
	 **/
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( $this->parent_class, array('abstract') );
		if($this->writable) {
			$this->variable( '_changed', array(), 'private' );
			$this->variable( '_oldkey', false, 'private' );
		}
		$this->write();

		$this->generate_common();

		/* Storage */
		foreach( $this->structure['structure'] as $field => $type ) {
			if( is_array($type) ) {
				$this->{"storage_object"}( $field, $type );
			} else {
				$this->{"storage_$type"}( $field );
			}
		}

		$this->generate_factory_from_setiterator();

		if($this->writable) {
			$this->generate_validate();
			$this->generate_save();
			$this->generate_delete();
		}

		$this->generate_get_key();

		/* Getters and setters */
		foreach( $this->structure['structure'] as $field => $type ) {
			if( is_array($type) ) {
				$this->{"get_object"}( $field, $type );
			} else {
				$this->{"get_$type"}( $field );
			}
			if( $this->writable ) {
				if( !is_array($type) ) {
					/* Only support setting of variables for now */
					$this->{"set_$type"}( $field );
				}
			}
		}

		foreach( $this->associations as $assoc ) {
			$this->generate_association_get_set( $assoc[0], $assoc[1], $assoc[2] );
		}

		$this->finish_class();
	}

	/**
	 * Generates a class construct
	 *
	 * @return void
	 **/
	private function generate_factory_from_setiterator() {
		/* Make it possible to generate empty objects if writable */
		$this->init_function( "factory_from_setiterator", array( 'values', 'prefix', 'export' ),  array('static'), array());

		$this->write( '$obj = new static();');

		$this->write( '$obj->export = array();' );
		$this->write( '$subobj_export = array();' );
		$this->write( 'if($export === false) $export = array();'); //FIXME
		$this->write( 'foreach( $export as $expcol ) {');
		$this->write(     '$parts = explode(".", $expcol, 2);');
		$this->write(     'if(count($parts) == 2) {');
		$this->write(         'if(!isset($subobj_export[$parts[0]])) {');
		$this->write(             '$subobj_export[$parts[0]] = array();');
		$this->write(         '}');
		$this->write(         '$subobj_export[$parts[0]][] = $parts[1];');
		$this->write(         '$obj->export[] = $parts[0];');
		$this->write(     '} else {');
		$this->write(         '$obj->export[] = $parts[0];');
		$this->write(     '}');
		$this->write( '}');
		$this->comment('If object fields exists, make sure the object only exists in the export array once');
		$this->write( '$obj->export = array_unique($obj->export);');
		foreach( $this->structure['structure'] as $field => $type ) {
			$backend_name = $field;
			if(isset($this->structure['rename']) && isset($this->structure['rename'][$field])) {
				$backend_name = $this->structure['rename'][$field];
			}
			if( is_array($type) ) {
				$this->{"fetch_object"}( $field, $backend_name, $type );
			} else {
				$this->{"fetch_$type"}( $field, $backend_name );
			}
		}
		if($this->writable) {
			$this->write( '$obj->_oldkey = array();');
			foreach($this->key as $name) {
				$this->write( "\$obj->_oldkey[%s] = \$obj->$name;", $name);
			}
		}
		$this->write( 'return $obj;');
		$this->finish_function();
	}

	/**
	 * Generate the validate method, if object is writable
	 *
	 * The validate method returns if the object is ok to save. Default is to
	 * always allow the object to be saved, but can be overridden.
	 */
	protected function generate_validate() {
		$this->init_function('validate', array(), array('protected'));
		$this->write('return true;');
		$this->finish_function();
	}

	/**
	 * Generate the save method, if object is writable
	 */
	private function generate_save() {
		$this->init_function('save');

		$this->write('if( empty($this->_changed) ) {');
		$this->write('return;');
		$this->write('}');

		$this->write('if( !$this->validate() ) {');
		$this->write('return;');
		$this->write('}');

		$this->write('$values = array();');
		foreach( $this->structure['structure'] as $name => $type ) {
			$this->write(   'if(isset($this->_changed[%s]) && $this->_changed[%s]) {', $name, $name);
			$this->write(     '$values[%s] = $this->'.$name.';', $name);
			$this->write(   '}');
		}
		$this->write(  'if($this->_oldkey === false) {'); // New
		$this->write(     '$insid = '.$this->pool_class.'::insert_single($values);');
		// Handle auto increment value, but only if single column key of type int
		if(count($this->key) == 1 && $this->structure['structure'][$this->key[0]] == 'int') {
			$this->write(     'if($insid !== false && $insid > 0) {');
			$this->write(        '$this->'.$this->key[0].' = $insid;');
			$this->write(     '}');
		}
		$this->write(  '} else {'); // Update
		$this->write(     '$set = '.$this->pool_class.'::all();');
		foreach($this->key as $name) {
			$this->write(     '$set = $set->reduce_by(%s, $this->_oldkey[%s], "=");', $name, $name);
		}
		$this->write(     '$set->update($values);');
		$this->write(  '}');
		$this->write(  '$this->_oldkey = array();');
		foreach($this->key as $name) {
			$this->write( "\$this->_oldkey[%s] = \$this->$name;", $name);
		}
		$this->write(  '$this->_changed = array();');
		$this->finish_function();
	}

	/**
	 * Generate the save method, if object is writable
	 */
	private function generate_delete() {
		$this->init_function('delete');
		$this->write(  'if($this->_oldkey === false) {'); // New
		$this->write(     'return;'); // Nothing to delete, it's not saved
		$this->write(  '}');
		$this->write(  '$set = '.$this->pool_class.'::all();');
		foreach($this->key as $name) {
			$this->write(  '$set = $set->reduce_by(%s, $this->_oldkey[%s], "=");', $name, $name);
		}
		$this->write(  '$set->delete($this->_oldkey);');
		$this->write(  '$this->_oldkey = false;');
		$this->write(  '$this->_changed = array();');
		$this->finish_function();
	}

	/**
	 * Generate association get set
	 *
	 * @param $table string
	 * @param $class string
	 * @param field string
	 * @return void
	 **/
	private function generate_association_get_set($table, $class, $field) {
		$this->init_function('get_'.$table.'_set');
		$this->write('$set = '.$class.'Pool'.self::$model_suffix.'::all();');
		foreach( $this->key as $key_field ) {
			$this->write('$set = $set->reduce_by(%s,$this->get_'.$key_field.'(),"=");', $field.'.'.$key_field);
		}
		$this->write('return $set;');
		$this->finish_function();
	}

	/**
	 * Generate get key
	 *
	 * @return void
	 **/
	private function generate_get_key() {
		$this->init_function("get_key");
		$matchline = '$key = $this%s;';
		$got = false;
		foreach( $this->key as $keypart ) {
			// Build getter sequence
			$call = "";
			foreach( explode('.',$keypart) as $part ) {
				$call .= "->get_$part()";
			}

			// Use sprintf instead of embedded in write. write escapes
			$this->write( sprintf( $matchline, $call ) );
			$got = true;
			$matchline = '$key .= ";".$this%s;';
		}
		if( $got ) {
			$this->write('return $key;');
		} else {
			$this->write('return false;');
		}
		$this->finish_function();
	}

	/**
	 * Writes a private variable to class
	 *
	 * @param $name string
	 * @param $type string		Unused?
	 * @return void
	 **/
	private function storage_object( $name, $type ) {
		$this->write( "private \$$name = false;" );
	}

	/**
	 * Writes an object fetcher
	 *
	 * @param $name string
	 * @param $type string
	 * @return void
	 **/
	private function fetch_object( $name, $backend_name, $type ) {
		list( $class, $prefix ) = $type;
		// Livestatus handles only one level of prefixes... might change in future? (for example comments: service.host.name should be host.name
		$this->write( "\$obj->$name = $class".self::$model_suffix."::factory_from_setiterator( \$values, %s, isset(\$subobj_export[%s]) ? \$subobj_export[%s] : array() );", $prefix, $backend_name, $name );
	}

	/**
	 * Writes function getting object named $name
	 *
	 * @param $name string
	 * @param $type string		Unused?
	 * @return void
	 **/
	private function get_object( $name, $type ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function storage_string( $name ) {
		$this->write( "private \$$name = false;" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function fetch_string( $name, $backend_name ) {
		$this->write( "if(array_key_exists(\$prefix.'$backend_name', \$values)) { ");
		$this->write( "\$obj->$name = (string)\$values[\$prefix.'$backend_name'];" );
		$this->write( "}" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function get_string( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function set_string( $name ) {
		$this->init_function( "set_$name", array('value') );
		$this->write( "\$value = (string)\$value;" );
		$this->write( "if( \$this->$name !== \$value ) {" );
		$this->write( "\$this->$name = \$value;" );
		$this->write( "\$this->_changed[%s] = true;", $name );
		$this->write( "}" );
		$this->finish_function();
	}

	/* Time (treated as int) */

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function storage_time( $name ) {
		$this->write( "private \$$name = false;" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function fetch_time( $name, $backend_name ) {
		$this->write( "if(array_key_exists(\$prefix.'$backend_name', \$values)) { ");
		$this->write( "\$obj->$name = intval( \$values[\$prefix.'$backend_name'] );" );
		$this->write( "}" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function get_time( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function set_time( $name ) {
		$this->init_function( "set_$name", array('value') );
		$this->write( "\$value = intval(\$value);" );
		$this->write( "if( \$this->$name !== \$value ) {" );
		$this->write( "\$this->$name = \$value;" );
		$this->write( "\$this->_changed[%s] = true;", $name );
		$this->write( "}" );
		$this->finish_function();
	}

	/* Int */

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function storage_int( $name ) {
		$this->write( "private \$$name = false;" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function fetch_int( $name, $backend_name ) {
		$this->write( "if(array_key_exists(\$prefix.'$backend_name', \$values)) {" );
		$this->write( "\$obj->$name = intval( \$values[\$prefix.'$backend_name'] );" );
		$this->write( "}" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function get_int( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function set_int( $name ) {
		$this->init_function( "set_$name", array('value') );
		$this->write( "\$value = intval(\$value);" );
		$this->write( "if( \$this->$name !== \$value ) {" );
		$this->write( "\$this->$name = \$value;" );
		$this->write( "\$this->_changed[%s] = true;", $name );
		$this->write( "}" );
		$this->finish_function();
	}

	/* Float */

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function storage_float( $name ) {
		$this->write( "private \$$name = false;" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function fetch_float( $name, $backend_name ) {
		$this->write( "if(array_key_exists(\$prefix.'$backend_name', \$values)) {" );
		$this->write( "\$obj->$name = floatval( \$values[\$prefix.'$backend_name'] );" );
		$this->write( "}" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function get_float( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function set_float( $name ) {
		$this->init_function( "set_$name", array('value') );
		$this->write( "\$value = floatval(\$value);" );
		$this->write( "if( \$this->$name !== \$value ) {" );
		$this->write( "\$this->$name = \$value;" );
		$this->write( "\$this->_changed[%s] = true;", $name );
		$this->write( "}" );
		$this->finish_function();
	}

	/* List */

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function storage_list( $name ) {
		$this->write( "private \$$name = false;" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function fetch_list( $name, $backend_name ) {
		$this->write( "if(array_key_exists(\$prefix.'$backend_name', \$values)) {" );
		$this->write( "\$obj->$name = \$values[\$prefix.'$backend_name'];" );
		$this->write( "}" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function get_list( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function set_list( $name ) {
		$this->init_function( "set_$name", array('value') );
		$this->write( "if( \$this->$name !== \$value ) {" );
		$this->write( "\$this->$name = \$value;" );
		$this->write( "\$this->_changed[%s] = true;", $name );
		$this->write( "}" );
		$this->finish_function();
	}

	/* Dict */

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function storage_dict( $name ) {
		$this->write( "private \$$name = false;" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function fetch_dict( $name, $backend_name ) {
		$this->write( "if(array_key_exists(\$prefix.'$backend_name', \$values)) {" );
		$this->write( "\$obj->$name = \$values[\$prefix.'$backend_name'];" );
		$this->write( "}" );
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function get_dict( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	private function set_dict( $name ) {
		$this->init_function( "set_$name", array('value') );
		$this->write( "if( \$this->$name !== \$value ) {" );
		$this->write( "\$this->$name = \$value;" );
		$this->write( "\$this->_changed[%s] = true;", $name );
		$this->write( "}" );
		$this->finish_function();
	}
}
