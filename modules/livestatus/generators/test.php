<?php

require_once('libraries/LivestatusAutoloader.php');
new LivestatusAutoloader();

$printers = HostPool::by_group( 'printers' );

$prob_hosts = HostPool::all();
$prob_hosts->reduceBy( new LivestatusFilterMatch( 'state', 1, '>=') );

$prob_servcies = ServicePool::all();
$prob_servcies->reduceBy( new LivestatusFilterMatch( 'state', 1, '>=') );

$prob_printers = $printers->intersect($prob_hosts);
$prob_or_printers = $printers->union($prob_hosts);

print "\n";
print $printers->test_getFormattedFilter()."\n";
print $prob_hosts->test_getFormattedFilter()."\n";
print $prob_printers->test_getFormattedFilter()."\n";
print $prob_or_printers->test_getFormattedFilter()."\n";