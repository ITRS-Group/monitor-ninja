<?php

class LivestatusBaseClassGenerator extends class_generator {

	private $name;
	private $structure;
	
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->structure = $descr['structure'];
		
		$this->classname = 'Base'.$descr['class'];
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( 'ObjectRoot', array('abstract') );
		$this->variable( '_table', $this->name, 'protected' );
		$this->write();
		
		/* Storage */
		foreach( $this->structure as $field => $type ) {
			if( is_array($type) ) {
				$this->{"storage_object"}( $field, $type );
			} else {
				$this->{"storage_$type"}( $field );
			}
		}
		
		$this->generate_construct();
		
		/* Getters and setters */
		foreach( $this->structure as $field => $type ) {
			if( is_array($type) ) {
				$this->{"getset_object"}( $field, $type );
			} else {
				$this->{"getset_$type"}( $field );
			}
		}
		
		$this->finish_class();
	}
	private function generate_construct() {
		$this->init_function( "__construct", array( 'values', 'prefix' ) );
		foreach( $this->structure as $field => $type ) {
			if( is_array($type) ) {
				$this->{"fetch_object"}( $field, $type );
			} else {
				$this->{"fetch_$type"}( $field );
			}
		}
		$this->finish_function();
	}
	
	/* Object */
	
	private function storage_object( $name, $type ) {
		$this->write( "private \$$name = false;" );
	}
	
	private function fetch_object( $name, $type ) {
		list( $class, $prefix ) = $type;
		$this->write( "\$this->$name = new $class".self::$model_suffix."( \$values, \$prefix.".var_export($prefix,true)." );" );
		$this->write( "\$this->export[] = %s;", $name );
	}
	
	private function getset_object( $name, $type ) {
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
	
	private function getset_string( $name ) {
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
	
	private function getset_time( $name ) {
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
	
	private function getset_int( $name ) {
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
	
	private function getset_float( $name ) {
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
	
	private function getset_list( $name ) {
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
	
	private function getset_dict( $name ) {
		$this->init_function( "get_$name" );
		$this->write( "return \$this->$name;" );
		$this->finish_function();
	}
}