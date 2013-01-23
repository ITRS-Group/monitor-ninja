<?php defined('SYSPATH') OR die('No direct access allowed.');

foreach( $vars as $var => $value ) {
	printf( "var %s = %s;\n", $var, json_encode($value));
}