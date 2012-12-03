<?php

require_once( 'LivestatusBaseClassGenerator.php' );
require_once( 'LivestatusBaseClassRootGenerator.php' );
require_once( 'LivestatusBasePoolClassGenerator.php' );
require_once( 'LivestatusWrapperClassGenerator.php' );
require_once( 'LivestatusStructure.php' );
require_once( 'LivestatusAutoloaderGenerator.php' );

class livestatusorm_generator extends generator_module {
	public function run() {
		
		$classpaths = array(
				'LivestatusAccess'      => 'libraries/LivestatusAccess.php',
				'LivestatusFilterOr'    => 'libraries/LivestatusFilterOr.php',
				'LivestatusFilterAnd'   => 'libraries/LivestatusFilterAnd.php',
				'LivestatusFilterBase'  => 'libraries/LivestatusFilterBase.php',
				'LivestatusFilterMatch' => 'libraries/LivestatusFilterMatch.php',
				'LivestatusFilterNot'   => 'libraries/LivestatusFilterNot.php',
				'LivestatusSet'         => 'libraries/LivestatusSet.php',
				'LivestatusSetIterator' => 'libraries/LivestatusSetIterator.php'
				);
		
		/* Generate base root class */
		$generator = new LivestatusBaseClassRootGenerator( 'root', array('class'=>'ObjectRoot') );
		$generator->generate();
		
		/* Generate root class wrapper */
		$generator = new LivestatusWrapperClassGenerator( 'root', array('class'=>'ObjectRoot', 'modifiers'=>array('abstract')) );
		if( !$generator->exists() )
			$generator->generate();
		
		foreach( LivestatusStructure::getTables() as $name => $structure ) {
			/* Generate base class */
			$generator = new LivestatusBaseClassGenerator( $name, $structure );
			$generator->generate();
			
			/* Generate base pool class */
			$generator = new LivestatusBasePoolClassGenerator( $name, $structure );
			$generator->generate();
		
			/* Generate wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( $name, $structure );
			if( !$generator->exists() )
				$generator->generate();
			
			/* Generate pool wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( $name, $structure, "%sPool" );
			if( !$generator->exists() )
				$generator->generate();
		}
		
		/* Generate autoloader */
		//$generator = new LivestatusAutoloaderGenerator( $classpaths );
		//$generator->generate();
	}
}