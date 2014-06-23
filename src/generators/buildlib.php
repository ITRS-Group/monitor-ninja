<?php

if( php_sapi_name() != 'cli' ) {
	print("Builders can only be runned as cli\n");
	exit(1);
}

define('KOHANA_BASE', dirname(dirname(dirname(__FILE__))) ); // FIXME: make nicer
define('TARGET_BASE', KOHANA_BASE . DIRECTORY_SEPARATOR . 'modules');
define('GENERATOR_BASE', KOHANA_BASE . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'generators');

require_once( 'op5/generators/class_generator.php' );

class_generator::$model_suffix = '_Model';
class_generator::$library_suffix = '';
class_generator::$library_dir = 'libraries';
class_generator::$model_dir = 'models';
class_generator::$manifest_dir = 'manifest';

abstract class generator_module {
	public $mod_name;
	public $mod_dir;
	public $gen_dir;

	public function __construct( $mod_name ) {
		$this->mod_name = $mod_name;
		$this->mod_dir  = TARGET_BASE . DIRECTORY_SEPARATOR . $mod_name;
		$this->gen_dir  = GENERATOR_BASE . DIRECTORY_SEPARATOR . $mod_name . DIRECTORY_SEPARATOR;
	}
	abstract protected function do_run();

	final public function run() {
		try {
			if( !is_dir( $this->mod_dir ) && !mkdir( $this->mod_dir, 0755 ) )
				gen_error( sprintf("Can not create '%s'", $this->mod_dir) );
			chdir( $this->mod_dir );

			$this->do_run();
		} catch( Exception $e ) {
			fprintf( STDERR, "Generator exception in module %s: %s\nExiting module...\n", $this->mod_name, $e->getMessage() );
			fprintf( STDERR, "%s @ %s\n%s\n", $e->getFile(), $e->getLine(), $e->getTraceAsString());
			exit( 1 );
		}
		exit( 0 );
	}
}
