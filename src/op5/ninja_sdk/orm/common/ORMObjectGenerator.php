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
	}

	/**
	 * Generate
	 *
	 * @return void
	 **/
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( 'Object', array('abstract') );
		$this->variable( '_table', $this->name, 'protected' );
		$this->write();

		/* Storage */
		foreach( $this->structure['structure'] as $field => $type ) {
			if( is_array($type) ) {
				$this->{"storage_object"}( $field, $type );
			} else {
				$this->{"storage_$type"}( $field );
			}
		}

		$this->generate_construct();
		$this->generate_get_key();

		/* Getters and setters */
		foreach( $this->structure['structure'] as $field => $type ) {
			if( is_array($type) ) {
				$this->{"get_object"}( $field, $type );
			} else {
				$this->{"get_$type"}( $field );
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
	private function generate_construct() {
		$this->init_function( "__construct", array( 'values', 'prefix', 'export' ) );
		$this->write( '$this->export = array();' );
		$this->write( '$subobj_export = array();' );
		$this->write( 'if($export === false) $export = array();'); //FIXME
		$this->write( 'foreach( $export as $expcol ) {');
		$this->write(     '$parts = explode(".", $expcol, 2);');
		$this->write(     'if(count($parts) == 2) {');
		$this->write(         'if(!isset($subobj_export[$parts[0]])) {');
		$this->write(             '$subobj_export[$parts[0]] = array();');
		$this->write(         '}');
		$this->write(         '$subobj_export[$parts[0]][] = $parts[1];');
		$this->write(         '$this->export[] = $parts[0];');
		$this->write(     '} else {');
		$this->write(         '$this->export[] = $parts[0];');
		$this->write(     '}');
		$this->write( '}');
		$this->comment('If object fields exists, make sure the object only exists in the export array once');
		$this->write( '$this->export = array_unique($this->export);');
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
		$this->write( 'parent::__construct( $values, $prefix, $export ); ');
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
		$this->write( "\$this->$name = new $class".self::$model_suffix."( \$values, %s, isset(\$subobj_export[%s]) ? \$subobj_export[%s] : array() );", $prefix, $backend_name, $name );
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
		$this->write( "\$this->$name = (string)\$values[\$prefix.'$backend_name'];" );
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

	/* Time FIXME */

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
		$this->write( "\$this->$name = \$values[\$prefix.'$backend_name'];" );
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
		$this->write( "\$this->$name = intval( \$values[\$prefix.'$backend_name'] );" );
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
		$this->write( "\$this->$name = floatval( \$values[\$prefix.'$backend_name'] );" );
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
		$this->write( "\$this->$name = \$values[\$prefix.'$backend_name'];" );
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
		$this->write( "\$this->$name = \$values[\$prefix.'$backend_name'];" );
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
}
