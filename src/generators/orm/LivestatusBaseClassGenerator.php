<?php

class LivestatusBaseClassGenerator extends class_generator {

	private $name;
	private $structure;
	private $associations;

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

	public function generate() {
		parent::generate();
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

	private function generate_association_get_set($table, $class, $field) {
		$this->init_function('get_'.$table.'_set');
		$this->write('$set = '.$class.'Pool'.self::$model_suffix.'::all();');
		foreach( $this->key as $key_field ) {
			$this->write('$set = $set.reduceBy(%s,$this->get_'.$key_field.'());', $field.'.'.$key_field);
		}
		$this->write('return $set;');
		$this->finish_function();
	}

	private function generate_get_key() {
		$this->init_function("get_key");
		$matchline = '$key = $this%s;';
		foreach( $this->key as $keypart ) {
			// Build getter sequence
			$call = "";
			foreach( explode('.',$keypart) as $part ) {
				$call .= "->get_$part()";
			}
				
			// Use sprintf instead of embedded in write. write escapes
			$this->write( sprintf( $matchline, $call ) );
			$matchline = '$key .= ";".$this%s;';
		}
		$this->write('return $key;');
		$this->finish_function();
	}

	/* Object */

	private function storage_object( $name, $type ) {
		$this->write( "private \$$name = false;" );
	}

	private function fetch_object( $name, $type ) {
		list( $class, $prefix ) = $type;
		//		$this->write( "\$this->$name = new $class".self::$model_suffix."( \$values, \$prefix.".var_export($prefix,true)." );" );
		// Livestatus handles only one level of prefixes... might change in future? (for example comments: service.host.name should be host.name
		$this->write( "\$this->$name = new $class".self::$model_suffix."( \$values, ".var_export($prefix,true)." );" );
		$this->write( "\$this->export[] = %s;", $name );
	}

	private function get_object( $name, $type ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/* String */

	private function storage_string( $name ) {
		$this->write( "private \$$name = false;" );
	}

	private function fetch_string( $name ) {
		$this->write( "if(isset(\$values[\$prefix.'$name'])) { ");
		$this->write( "\$this->$name = \$values[\$prefix.'$name'];" );
		$this->write( "\$this->export[] = %s;", $name );
		$this->write( "}" );
	}

	private function get_string( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/* Time FIXME */

	private function storage_time( $name ) {
		$this->write( "private \$$name = false;" );
	}

	private function fetch_time( $name ) {
		$this->write( "if(isset(\$values[\$prefix.'$name'])) { ");
		$this->write( "\$this->$name = \$values[\$prefix.'$name'];" );
		$this->write( "\$this->export[] = %s;", $name );
		$this->write( "}" );
	}

	private function get_time( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/* Int */

	private function storage_int( $name ) {
		$this->write( "private \$$name = false;" );
	}

	private function fetch_int( $name ) {
		$this->write( "if(isset(\$values[\$prefix.'$name'])) {" );
		$this->write( "\$this->$name = intval( \$values[\$prefix.'$name'] );" );
		$this->write( "\$this->export[] = %s;", $name );
		$this->write( "}" );
	}

	private function get_int( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/* Float */

	private function storage_float( $name ) {
		$this->write( "private \$$name = false;" );
	}

	private function fetch_float( $name ) {
		$this->write( "if(isset(\$values[\$prefix.'$name'])) {" );
		$this->write( "\$this->$name = floatval( \$values[\$prefix.'$name'] );" );
		$this->write( "\$this->export[] = %s;", $name );
		$this->write( "}" );
	}

	private function get_float( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/* List */

	private function storage_list( $name ) {
		$this->write( "private \$$name = false;" );
	}

	private function fetch_list( $name ) {
		$this->write( "if(isset(\$values[\$prefix.'$name'])) {" );
		$this->write( "\$this->$name = floatval( \$values[\$prefix.'$name'] );" );
		$this->write( "\$this->export[] = %s;", $name );
		$this->write( "}" );
	}

	private function get_list( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}

	/* Dict */

	private function storage_dict( $name ) {
		$this->write( "private \$$name = false;" );
	}

	private function fetch_dict( $name ) {
		$this->write( "if(isset(\$values[\$prefix.'$name'])) {" );
		$this->write( "\$this->$name = floatval( \$values[\$prefix.'$name'] );" );
		$this->write( "\$this->export[] = %s;", $name );
		$this->write( "}" );
	}

	private function get_dict( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}
}