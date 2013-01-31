<?php defined('SYSPATH') OR die('No direct access allowed.');
header('Content-Type: text/javascript');
foreach( $vars as $var => $value ) {
	printf( "var %s = %s;\n", $var, json_encode($value));
}