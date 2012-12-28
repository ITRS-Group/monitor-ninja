<?php

class GeneratorException extends Exception {}

abstract class generator_module {
	public $mod_name;
	public $mod_dir;
	public $gen_dir;

	public function __construct( $mod_name, $mod_dir, $gen_dir ) {
		$this->mod_name = $mod_name;
		$this->mod_dir  = $mod_dir;
		$this->gen_dir  = $gen_dir;
	}

	public function install_template() {
	}

	abstract function run();
}

require_once( 'class_generator.php' );
require_once( 'js_class_generator.php' );

$exit_code = 0;

$mods = $_SERVER['argv'];
array_shift($mods);

try {
	$mod_name = "Generator";

	$gen_root = dirname(__FILE__);
	printf( "generator root = %s\n", $gen_root );

	$mod_root = realpath( dirname(__FILE__) . DIRECTORY_SEPARATOR . "../../modules" );
	if( $mod_root === false ) {
		throw new GeneratorException( "modules root '$mod_root' could not be found" );
	}

	printf( "modules root   = %s\n", $mod_root );

	$old_include_path = get_include_path();


	foreach( scandir( $gen_root ) as $mod_name ) {
		$gen_dir = $gen_root . DIRECTORY_SEPARATOR . $mod_name . DIRECTORY_SEPARATOR;
		$run_script = $gen_dir . "run_mod.php";
		$mod_dir = $mod_root . DIRECTORY_SEPARATOR . $mod_name . DIRECTORY_SEPARATOR;
		if( !is_dir( $gen_dir ) || !file_exists( $run_script ) ) {
			continue;
		}
		if(!empty($mods) && !in_array($mod_name, $mods)) {
			printf( "Skipping %-20s at %s\n", $mod_name, $mod_dir );
			continue;
		}
		try {
			printf( "Building %-20s at %s\n", $mod_name, $mod_dir );
			set_include_path( $old_include_path
			. PATH_SEPARATOR . $gen_dir
			. PATH_SEPARATOR . $gen_root );

			if( !is_dir( $mod_dir ) && !mkdir( $mod_dir, 0755 ) )
				gen_error( "Can not create '$mod_dir'" );
			chdir( $mod_dir );

			require( $run_script );

			$generator_name = $mod_name . '_generator';
			$obj = new $generator_name( $mod_name, $mod_dir, $gen_dir );
			$template_dir = $gen_dir . 'template' . DIRECTORY_SEPARATOR;
			if( is_dir( $template_dir ) ) {
				$obj->install_template( $template_dir );
			}
			$obj->run();
		}
		catch( GeneratorException $e ) {
			fprintf( STDERR, "Generator exception in module %s: %s\nExiting module...\n", $mod_name, $e->getMessage() );
			fprintf( STDERR, "%s @ %s\n%s\n", $e->getFile(), $e->getLine(), $e->getTraceAsString());
			$exit_code = 2;
		}
	}
}
catch( GeneratorException $e ) {
	fprintf( "Generator exception: %s\nExiting module...\n",$e->getMessage() );
	$exit_code = 1;
}

exit( $exit_code );