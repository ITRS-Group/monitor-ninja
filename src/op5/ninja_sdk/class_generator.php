<?php

require_once( 'generator_lib.php' );

abstract class class_generator {
	protected $fp;
	protected $indent_lvl = array();
	protected $class_suffix = '';
	protected $moduledir = false;
	protected $class_dir = false;
	protected $class_basedir = false;
	protected $classname;

	/* Overwrite those to make compatible with framework naming convention */
	public static $model_suffix = '_Model';
	public static $library_suffix = '';
	public static $library_dir = 'libraries';
	public static $model_dir = 'models';
	public static $manifest_dir = 'manifest';

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

	public function generate_getter($name) {
		$this->init_function('get_'.$name);
		$this->write('return $this->'.$name.';');
		$this->finish_function();
	}

	public function set_class_suffix( $class_suffix ) {
		$this->class_suffix = $class_suffix;
	}

	public function set_class_dir( $class_dir ) {
		$this->class_dir = $class_dir;
	}

	public function set_moduledir( $moduledir ) {
		$this->moduledir = $moduledir;
	}

	public function set_basedir( $class_basedir ) {
		$this->class_basedir = $class_basedir;
	}

	public function set_library() {
		$this->set_class_suffix( self::$library_suffix );
		$this->set_basedir( self::$library_dir );
		$this->filename_lowercase = false;
	}

	public function set_model() {
		$this->set_class_suffix( self::$model_suffix );
		$this->set_basedir( self::$model_dir );
	}

	public function set_manifest() {
		$this->set_class_suffix( '' );
		$this->set_basedir( self::$manifest_dir );
	}

	public function exists() {
		return file_exists( $this->get_filename() );
	}

	public function get_classname() {
		return $this->classname . $this->class_suffix;
	}

	public function get_include_path() {
		$filename = $this->classname;
		if( $this->filename_lowercase ) {
			$filename = strtolower( $this->classname );
		}
		$path = '';
		if($this->class_dir !== false) {
			$path .= $this->class_dir . DIRECTORY_SEPARATOR;
		}
		return $path . $filename . '.php';
	}

	public function get_filename() {
		$filename = $this->classname;
		if( $this->filename_lowercase ) {
			$filename = strtolower( $this->classname );
		}
		$path = '';
		if($this->moduledir !== false) {
			$path .= $this->moduledir . DIRECTORY_SEPARATOR;
		}
		if($this->class_basedir !== false) {
			$path .= $this->class_basedir . DIRECTORY_SEPARATOR;
		}
		if($this->class_dir !== false) {
			$path .= $this->class_dir . DIRECTORY_SEPARATOR;
		}
		return $path . $filename . '.php';
	}

	public function classfile( $path, $relative_to_file=false ) {
		if($relative_to_file) {
			$filephp = var_export(DIRECTORY_SEPARATOR.$path, true);
			$filephp = 'dirname(__FILE__).'.$filephp;
		} else {
			$filephp = var_export($path, true);
		}
		$this->write( 'require_once( '.$filephp.' );' );
	}

	public function init_class( $parent = false, $modifiers = array(), $interfaces = false ) {
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
		$this->write( "/**\n * Autogenerated class ".$this->get_classname()."\n *\n * @todo: documentation\n */" );
		$this->write( $modifiers."class ".$this->get_classname().($parent===false?"":" extends ".$parent.$this->class_suffix).$interface_str." {" );
	}

	public function finish_class() {
		$this->write( "}" );
	}

	public function variable( $name, $default = null, $visibility = 'private' ) {

		if( false!==strpos( $visibility, 'public' ) || false!==strpos( $visibility, 'protected' ) ) {
			$this->write( "/**\n * Autogenerated varible\n *\n * @todo: documentation\n */" );
		}
		$this->write( "$visibility \$$name = " . var_export( $default, true ) . ";" );
	}

	public function abstract_function( $name, $args = array(), $modifiers = array(), $defaults = array() ) {
		if( !is_array( $modifiers ) ) {
			$modifiers = array_filter( array_map( 'trim', explode(' ',$modifiers) ) );
		}
		if( !in_array('public',$modifiers) && !in_array('private',$modifiers) && !in_array('protected',$modifiers) ) {
			$modifiers[] = 'public';
		}
		$argstr = "";
		$argdelim = "";
		foreach( $args as $arg ) {
			$argstr .= $argdelim.'$'.$arg;
			if( isset($defaults[$arg]) )
				$argstr .= '='.var_export($defaults[$arg],true);
			$argdelim = ", ";
		}

		if( in_array('public',$modifiers) || in_array('protected',$modifiers) ) {
			$this->write( "/**\n * Autogenerated function\n *\n * @todo: documentation\n */" );
		}
		$modifiers = implode( ' ', $modifiers );
		if( !empty( $modifiers ) ) {
			$modifiers = trim($modifiers)." ";
		}
		$this->write( "abstract ${modifiers}function $name($argstr);" );
		$this->write();
	}

	public function init_function( $name, $args = array(), $modifiers = array(), $defaults = array(), $attr = false ) {
		if( !is_array( $modifiers ) ) {
			$modifiers = array_filter( array_map( 'trim', explode(' ',$modifiers) ) );
		}
		if( !in_array('public',$modifiers) && !in_array('private',$modifiers) && !in_array('protected',$modifiers) ) {
			$modifiers[] = 'public';
		}
		$argstr = "";
		$argdelim = "";
		foreach( $args as $arg ) {
			$argstr .= $argdelim.'$'.$arg;
			if( isset($defaults[$arg]) )
				$argstr .= '='.var_export($defaults[$arg],true);
			$argdelim = ", ";
		}

		if( in_array('public',$modifiers) || in_array('protected',$modifiers) ) {
			$this->write( "/**\n * Autogenerated function\n *\n * @todo: documentation\n */" );
		}
		$modifiers = implode( ' ', $modifiers );
		if( !empty( $modifiers ) ) {
			$modifiers = trim($modifiers)." ";
		}
		if($attr==false ? $this->write( "{$modifiers}function $name($argstr) {" ) : $this->write( "{$modifiers}function $name($argstr) : $attr {" ));
	}

	public function finish_function() {
		$this->write( "}" );
		$this->write();
	}

	public function comment( $comment ) {
		$lines = explode( "\n", $comment );
		$curlvl = array_sum( $this->indent_lvl );
		foreach( $lines as $line ) {
			fwrite( $this->fp, str_repeat( "\t", $curlvl ) . "// " . trim($line) . "\n" );
		}
	}

	public function conditional () {
		$conditions = func_get_args();
		$expression = implode(" || ", array_map(function ($cond) {return "($cond)";}, $conditions));
		$this->write("if ($expression) {");
	}

	public function raise ($exception, $message = "") {
		$this->write("throw new $exception($message);");
	}

	public function write( $block = '' ) {
		$args = func_get_args();
		$block = array_shift( $args );
		$args_str = array_map( function($var){
			return var_export($var,true);
		}, $args );
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
