<?php
if (php_sapi_name() != 'cli') {
	print ("Builders can only be runned as cli\n") ;
	exit( 1 );
}

require_once (__DIR__ . "/src/op5/ninja_sdk/Ninja_Builder.php");

$builder = new Ninja_Builder();

foreach ( scandir( "modules" ) as $module ) {
	if ($module[0] == '.')
		continue;
	$builder->add_module( "modules/$module" );
}
$builder->generate();