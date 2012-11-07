<?php

require_once( 'LivestatusBaseClassGenerator.php' );
require_once( 'LivestatusBaseClassRootGenerator.php' );
require_once( 'LivestatusBasePoolClassGenerator.php' );
require_once( 'LivestatusWrapperClassGenerator.php' );
require_once( 'LivestatusStructure.php' );
require_once( 'LivestatusAutoloaderGenerator.php' );

$outdir = 'models/';
$outdir_base = $outdir . 'base/';
$outdir_lib = 'libraries/';

$classpaths = array(
		'LivestatusFilterOr' => 'libraries/LivestatusFilterOr.php',
		'LivestatusFilterAnd' => 'libraries/LivestatusFilterAnd.php',
		'LivestatusFilterBase' => 'libraries/LivestatusFilterBase.php',
		'LivestatusFilterMatch' => 'libraries/LivestatusFilterMatch.php',
		'LivestatusFilterNot' => 'libraries/LivestatusFilterNot.php',
		'LivestatusSet' => 'libraries/LivestatusSet.php'
		);

/* Generate base root class */
$generator = new LivestatusBaseClassRootGenerator( 'root', array('class'=>'ObjectRoot') );
$filename = $outdir_base.$generator->get_classname().'.php';
$classpaths[$generator->get_classname()] = $filename;
$outp = fopen( $filename,'w' );
$generator->generate( $outp );

/* Generate root class wrapper */
$generator = new LivestatusWrapperClassGenerator( 'root', array('class'=>'ObjectRoot', 'modifiers'=>array('abstract')) );
$filename = $outdir.$generator->get_classname().'.php';
$classpaths[$generator->get_classname()] = $filename;
if( !file_exists( $filename ) ) {
	$outp = fopen( $filename,'w' );
	$generator->generate( $outp );
}

foreach( LivestatusStructure::getTables() as $name => $structure ) {
	/* Generate base class */
	$generator = new LivestatusBaseClassGenerator( $name, $structure );
	$filename = $outdir_base.$generator->get_classname().'.php';
	$classpaths[$generator->get_classname()] = $filename;
	$outp = fopen( $filename,'w' );
	$generator->generate( $outp );
	
	/* Generate base pool class */
	$generator = new LivestatusBasePoolClassGenerator( $name, $structure );
	$filename = $outdir_base.$generator->get_classname().'.php';
	$classpaths[$generator->get_classname()] = $filename;
	$outp = fopen( $filename,'w' );
	$generator->generate( $outp );

	/* Generate wrapper if not exists */
	$generator = new LivestatusWrapperClassGenerator( $name, $structure );
	$filename = $outdir.$generator->get_classname().'.php';
	$classpaths[$generator->get_classname()] = $filename;
	if( !file_exists( $filename ) ) {
		$outp = fopen( $filename,'w' );
		$generator->generate( $outp );
	}
	
	/* Generate pool wrapper if not exists */
	$generator = new LivestatusWrapperClassGenerator( $name, $structure, "%sPool" );
	$filename = $outdir.$generator->get_classname().'.php';
	$classpaths[$generator->get_classname()] = $filename;
	if( !file_exists( $filename ) ) {
		$outp = fopen( $filename,'w' );
		$generator->generate( $outp );
	}
}

/* Generate wrapper if not exists */
$generator = new LivestatusAutoloaderGenerator( $classpaths );
$filename = $outdir_lib.$generator->get_classname().'.php';
$outp = fopen( $filename,'w' );
$generator->generate( $outp );

print_r( $classpaths );