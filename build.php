<?php
if (php_sapi_name() != 'cli') {
	print ("Builders can only be runned as cli\n") ;
	exit( 1 );
}

require_once (__DIR__ . "/src/op5/ninja_sdk/Ninja_Builder.php");

$builder = new Ninja_Builder();

$modules = false;

/* First, get module names from command line */
if($modules === false && count($argv) > 1) {
	$modules = array_slice($argv, 1);
}

/* If no modules found on command line, scan modules dir */
if($modules === false) {
	$modules = scandir( "modules" );
}


foreach ( $modules as $module ) {
	if ($module[0] == '.')
		continue;
	$builder->add_module( "modules/$module" );
}
$builder->generate();