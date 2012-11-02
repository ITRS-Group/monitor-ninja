<?php

require_once( 'LivestatusBaseClassGenerator.php' );
require_once( 'LivestatusWrapperClassGenerator.php' );
require_once( 'LivestatusStructure.php' );

$outdir = 'models/';
$outdir_base = $outdir . 'base/';

foreach( LivestatusStructure::getTables() as $name => $structure ) {
	/* Generate base class */
	$generator = new LivestatusBaseClassGenerator( $name, $structure );
	$filename = $outdir_base.$generator->get_classname().'.php';
	$outp = fopen( $filename,'w' );
	$generator->generate( $outp );
	
	/* Generate wrapper if not exists */
	$generator = new LivestatusWrapperClassGenerator( $name, $structure );
	$filename = $outdir.$generator->get_classname().'.php';
	if( !file_exists( $filename ) ) {
		$outp = fopen( $filename,'w' );
		$generator->generate( $outp );
	}
}