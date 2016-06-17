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
	$rules = array_slice($argv, 1);
}

/* If no rules is specified, load everything */
if(empty($rules)) {
	$rules = array(':');
}

/* If no modules found on command line, scan modules dir */
$all_modules = array();
foreach( scandir( "modules" ) as $module ) {
	if( $module[0] == '.' )
		continue;
	$all_modules[$module] = 'modules/'.$module;
};
$all_modules['application'] = 'application';


foreach ( $rules as $rule ) {
	$parts = explode(':', $rule);

	/* If module is specified, only use that, otherwise all */
	if(!empty($parts[0])) {
		if( !isset($all_modules[$parts[0]]) ) {
			print "Ignoring unknown module {$parts[0]}\n";
			continue;
		}
		$moduledirs = array( $all_modules[$parts[0]] );
	} else {
		$moduledirs = array_values( $all_modules );
	}

	/* If target is specified, only use that */
	$target = null;
	if(count($parts) >= 2)
		$target = $parts[1];

	/* Add rule to builder */
	foreach($moduledirs as $moduledir) {
		$builder->add_module( $moduledir, $target );
	}
}
$builder->generate();
