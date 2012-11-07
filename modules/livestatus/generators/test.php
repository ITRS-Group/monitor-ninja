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

$all = HostPool::all();
$all->reduceBy( new LivestatusFilterMatch( 'state', 999, '!=' ) );

foreach( $all as $printer ) {
	echo "Name: ".$printer->get_name()."\n";
}