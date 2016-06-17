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
$all_modules = array_filter(
	scandir( "modules" ),
	function($line) {
		return $line[0] != '.';
	}
);


foreach ( $rules as $rule ) {
	$parts = explode(':', $rule);

	/* If module is specified, only use that, otherwise all */
	if(!empty($parts[0])) {
		$modules = array($parts[0]);
	} else {
		$modules = $all_modules;
	}

	/* If target is specified, only use that */
	$target = null;
	if(count($parts) >= 2)
		$target = $parts[1];

	/* Add rule to builder */
	foreach($modules as $module) {
		$builder->add_module( "modules/".$module, $target );
	}
}
$builder->generate();
