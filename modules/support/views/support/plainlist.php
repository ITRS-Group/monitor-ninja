<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $data array */
header('Content-Type: text/plain');

foreach( $data as $command => $output ) {
	print str_repeat("=", 72) . "\n";
	print "$ $command\n";
	print "\n";
	print "$output\n";
	print "\n\n";
}
