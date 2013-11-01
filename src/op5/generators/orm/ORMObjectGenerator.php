<?php

class ORMObjectGenerator extends class_generator {

	private $name;
	private $structure;
	private $associations;

	/**
	 * Construct
	 *
	 * @return void
	 **/
	public function __construct( $name, $structure ) {
		$this->name = $name;
		$this->structure = $structure[$name];
		$this->key = $this->structure['key'];
		$this->classname = 'Base'.$this->structure['class'];

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

		$this->set_model();
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
		$this->init_function( "__construct", array( 'values', 'prefix' ) );
		foreach( $this->structure['structure'] as $field => $type ) {
			if( is_array($type) ) {
				$this->{"fetch_object"}( $field, $type );
			} else {
				$this->{"fetch_$type"}( $field );
			}
		}
		$this->write( 'parent::__construct( $values, $prefix ); ');
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
	private function fetch_object( $name, $type ) {
		list( $class, $prefix ) = $type;
		//		$this->write( "\$this->$name = new $class".self::$model_suffix."( \$values, \$prefix.".var_export($prefix,true)." );" );
		// Livestatus handles only one level of prefixes... might change in future? (for example comments: service.host.name should be host.name
		$this->write( "\$this->$name = new $class".self::$model_suffix."( \$values, ".var_export($prefix,true)." );" );
		$this->write( "\$this->export[] = %s;", $name );
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
	private function fetch_string( $name ) {
		$this->write( "if(array_key_exists(\$prefix.'$name', \$values)) { ");
		$this->write( "\$this->$name = (string)\$values[\$prefix.'$name'];" );
		$this->write( "\$this->export[] = %s;", $name );
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
	private function fetch_time( $name ) {
		$this->write( "if(array_key_exists(\$prefix.'$name', \$values)) { ");
		$this->write( "\$this->$name = \$values[\$prefix.'$name'];" );
		$this->write( "\$this->export[] = %s;", $name );
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
	private function fetch_int( $name ) {
		$this->write( "if(array_key_exists(\$prefix.'$name', \$values)) {" );
		$this->write( "\$this->$name = intval( \$values[\$prefix.'$name'] );" );
		$this->write( "\$this->export[] = %s;", $name );
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
	private function fetch_float( $name ) {
		$this->write( "if(array_key_exists(\$prefix.'$name', \$values)) {" );
		$this->write( "\$this->$name = floatval( \$values[\$prefix.'$name'] );" );
		$this->write( "\$this->export[] = %s;", $name );
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
	private function fetch_list( $name ) {
		$this->write( "if(array_key_exists(\$prefix.'$name', \$values)) {" );
		$this->write( "\$this->$name = \$values[\$prefix.'$name'];" );
		$this->write( "\$this->export[] = %s;", $name );
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
	private function fetch_dict( $name ) {
		$this->write( "if(array_key_exists(\$prefix.'$name', \$values)) {" );
		$this->write( "\$this->$name = \$values[\$prefix.'$name'];" );
		$this->write( "\$this->export[] = %s;", $name );
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
