<?php

abstract class generator_module {
	public $mod_name;
	public $mod_dir;
	public $gen_dir;
	
	public function __construct( $mod_name, $mod_dir, $gen_dir ) {
		$this->mod_name = $mod_name;
		$this->mod_dir  = $mod_dir;
		$this->gen_dir  = $gen_dir;
		if( !is_dir( $mod_dir ) && !mkdir( $mod_dir, 0755 ) )
			gen_error( "Can not create '$mod_dir'" );
	}
	
	public function gen_error( $msg ) {
		fprintf( STDERR, "ERROR @ %s: %s\n", $this->mod_name, $msg );
		exit(1);
	}
	
	abstract function run();
}

$mod_name = "Generator";

$gen_root = dirname(__FILE__);
printf( "generator root = %s\n", $gen_root );

$mod_root = realpath( dirname(__FILE__) . DIRECTORY_SEPARATOR . "../../modules" );
if( $mod_root === false ) gen_error( "modules root '$mod_root' could not be found" );

printf( "modules root   = %s\n", $mod_root );

$old_include_path = get_include_path();


foreach( scandir( $gen_root ) as $mod_name ) {
	$gen_dir = $gen_root . DIRECTORY_SEPARATOR . $mod_name;
	$run_script = $gen_dir . DIRECTORY_SEPARATOR . "run_mod.php";
	if( is_dir( $gen_dir ) && file_exists( $run_script ) ) {
		$mod_dir = $mod_root . DIRECTORY_SEPARATOR . $mod_name . DIRECTORY_SEPARATOR;
		printf( "Building %-20s at %s\n", $mod_name, $mod_dir );
		set_include_path($old_include_path . PATH_SEPARATOR . $gen_dir );
		chdir( $mod_dir );
		require( $run_script );
		
		$generator_name = $mod_name . '_generator';
		$obj = new $generator_name( $mod_name, $mod_dir, $gen_dir );
		$obj->run();
	}
}
