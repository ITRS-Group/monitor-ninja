<?php

abstract class class_generator {
	protected $fp;
	protected $indent_lvl = array();
	protected $class_suffix = '';
	protected $class_dir = '.';
	protected $class_basedir = '.';
	protected $classname;
	
	protected static $model_suffix = '_Model';
	protected static $library_suffix = '_Core';
	protected $filename_lowercase = true;
	
	public function generate( $skip_generated_note = false ) {
		$class_dir = dirname( $this->get_filename() );
		
		if( !is_dir( $class_dir ) && !mkdir( $class_dir, 0755, true ) )
			throw new GeneratorException( "Could not create dir $class_dir" );
		
		$this->fp = fopen( $this->get_filename(), 'w' );

		if( $this->fp === false )
			throw new GeneratorException( "Could not open ".$this->get_filename()." for writing" );
		
		/* Hardcode, so we don't accidentaly add whitespace before < */
		fwrite( $this->fp, "<?php\n\n" );
		
		if( !$skip_generated_note )
			$this->comment( "\nNOTE!\n\nThis is an auto generated file. Changes to this file will be overwritten!\n" );
	}
	
	public function set_class_suffix( $class_suffix ) {
		$this->class_suffix = $class_suffix;
	}
	
	public function set_class_dir( $class_dir ) {
		$this->class_dir = $class_dir;
	}
	
	public function set_basedir( $class_basedir ) {
		$this->class_basedir = $class_basedir;
	}
	
	public function set_library() {
		$this->set_class_suffix( self::$library_suffix );
		$this->set_class_dir( 'libraries' );
		$this->filename_lowercase = false;
	}
	
	public function set_model() {
		$this->set_class_suffix( self::$model_suffix );
		$this->set_class_dir( 'models' );
	}
	
	public function exists() {
		return file_exists( $this->get_filename() );
	}
	
	public function get_classname() {
		return $this->classname . $this->class_suffix;
	}
	
	protected function get_filename() {
		$filename = $this->classname;
		if( $this->filename_lowercase ) {
			$filename = strtolower( $this->classname );
		}
		return $this->class_basedir . DIRECTORY_SEPARATOR . $this->class_dir . DIRECTORY_SEPARATOR . $filename . '.php';
	}
	
	protected function classfile( $path ) {
		$this->write( 'requrire_once( '. var_export($path, true) . ' );' );
	}
	
	protected function init_class( $parent = false, $modifiers = array(), $interfaces = false ) {
		if( is_array( $modifiers ) ) {
			$modifiers = implode( ' ', $modifiers );
		}
		if( !empty( $modifiers ) ) {
			$modifiers = trim($modifiers)." ";
		}
		$interface_str = "";
		if( $interfaces !== false ) {
			$interface_str = " implements ".implode(', ',$interfaces);
		}
		
		$this->write();
		$this->write( $modifiers."class ".$this->get_classname().($parent===false?"":" extends ".$parent.$this->class_suffix).$interface_str." {" );
	}
	
	protected function finish_class() {
		$this->write( "}" );
	}
	
	protected function variable( $name, $default = null, $visibility = 'private' ) {
		$this->write( "$visibility \$$name = " . var_export( $default, true ) . ";" );
	}
	
	protected function abstract_function( $name, $args = array(), $modifiers = array(), $defaults = array() ) {
		if( !is_array( $modifiers ) ) {
			$modifiers = array_filter( array_map( 'trim', explode(' ',$modifiers) ) );
		}
		if( !in_array('public',$modifiers) && !in_array('private',$modifiers) && !in_array('protected',$modifiers) ) {
			$modifiers[] = 'public';
		}
		$modifiers = implode( ' ', $modifiers );
		if( !empty( $modifiers ) ) {
			$modifiers = trim($modifiers)." ";
		}
		$argstr = "";
		$argdelim = "";
		foreach( $args as $arg ) {
			$argstr .= $argdelim.'$'.$arg;
			if( isset($defaults[$arg]) )
				$argstr .= '='.var_export($defaults[$arg],true);
			$argdelim = ", ";
		}
		$this->write( "abstract ${modifiers}function $name($argstr);" );
		$this->write();
	}
	
	protected function init_function( $name, $args = array(), $modifiers = array(), $defaults = array() ) {
		if( !is_array( $modifiers ) ) {
			$modifiers = array_filter( array_map( 'trim', explode(' ',$modifiers) ) );
		}
		if( !in_array('public',$modifiers) && !in_array('private',$modifiers) && !in_array('protected',$modifiers) ) {
			$modifiers[] = 'public';
		}
		$modifiers = implode( ' ', $modifiers );
		if( !empty( $modifiers ) ) {
			$modifiers = trim($modifiers)." ";
		}
		$argstr = "";
		$argdelim = "";
		foreach( $args as $arg ) {
			$argstr .= $argdelim.'$'.$arg;
			if( isset($defaults[$arg]) )
				$argstr .= '='.var_export($defaults[$arg],true);
			$argdelim = ", ";
		}
		$this->write( "${modifiers}function $name($argstr) {" );
	}
	
	protected function finish_function() {
		$this->write( "}" );
		$this->write();
	}
	
	protected function comment( $comment ) {
		$lines = explode( "\n", $comment );
		$curlvl = array_sum( $this->indent_lvl );
		foreach( $lines as $line ) {
			fwrite( $this->fp, str_repeat( "\t", $curlvl ) . "// " . trim($line) . "\n" );
		}
	}
	protected function write( $block = '' ) {
		$args = func_get_args();
		$block = array_shift( $args );
		$args_str = array_map( function($var){return var_export($var,true);}, $args );
		$block = vsprintf($block,$args_str);
		
		$lines = explode( "\n", $block );
		foreach( $lines as $line ) {
			for($i=substr_count( $line, '}' ); $i>0; $i--)
				array_pop( $this->indent_lvl ); 
			$curlvl = array_sum( $this->indent_lvl );
			if( substr( trim($line), 0, 4) == 'case' ) $curlvl--;
			fwrite( $this->fp, str_repeat( "\t", $curlvl ) . $line . "\n" );
			for($i=substr_count( $line, '{' ); $i>0; $i--)
				$this->indent_lvl[] = (strpos( $line, "switch" ) !== false) ? 2 : 1; 
		}
	}
}