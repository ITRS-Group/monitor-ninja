<?php

require_once( 'LivestatusBaseClassGenerator.php' );
require_once( 'LivestatusBaseClassRootGenerator.php' )
;
require_once( 'LivestatusBasePoolClassGenerator.php' );
require_once( 'LivestatusBaseRootPoolClassGenerator.php' );

require_once( 'LivestatusBaseSetClassGenerator.php' );
require_once( 'LivestatusBaseRootSetClassGenerator.php' );

require_once( 'LivestatusWrapperClassGenerator.php' );
require_once( 'LivestatusJSStructureGenerator.php' );
require_once( 'LivestatusAutoloaderGenerator.php' );

require_once( 'LivestatusStructure.php' );

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
		
		$full_structure = LivestatusStructure::getTables();
		
		/* Generate base root class */
		$generator = new LivestatusBaseClassRootGenerator( 'root', array('class'=>'ObjectRoot') );
		$generator->generate();
			
		/* Generate base pool class */
		$generator = new LivestatusBaseRootPoolClassGenerator( 'root', $full_structure );
		$generator->generate();
		
		/* Generate base set class */
		$generator = new LivestatusBaseRootSetClassGenerator( 'root', $full_structure );
		$generator->generate();
		
		/* Generate root class wrapper */
		$generator = new LivestatusWrapperClassGenerator( 'root', array('class'=>'ObjectRoot', 'modifiers'=>array('abstract')) );
		if( !$generator->exists() )
			$generator->generate();
			
		/* Generate pool class wrapper if not exists */
		$generator = new LivestatusWrapperClassGenerator( 'root', array('class'=>'ObjectPool', 'modifiers'=>array('abstract')) );
		if( !$generator->exists() )
			$generator->generate();
		
		/* Generate set class wrapper if not exists */ 
		$generator = new LivestatusWrapperClassGenerator( 'root', array('class'=>'ObjectSet', 'modifiers'=>array('abstract')) );
		if( !$generator->exists() )
			$generator->generate();
		
		foreach( $full_structure as $name => $structure ) {
			/* Generate base class */
			$generator = new LivestatusBaseClassGenerator( $name, $structure );
			$generator->generate();
			
			/* Generate base pool class */
			$generator = new LivestatusBasePoolClassGenerator( $name, $full_structure );
			$generator->generate();
			
			/* Generate base set class */
			$generator = new LivestatusBaseSetClassGenerator( $name, $structure );
			$generator->generate();
			
			/* Generate wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( $name, $structure );
			if( !$generator->exists() )
				$generator->generate();
			
			/* Generate pool wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( $name, $structure, "%sPool" );
			if( !$generator->exists() )
				$generator->generate();
			
			/* Generate set wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( $name, $structure, "%sSet" );
			if( !$generator->exists() )
				$generator->generate();
				
				
		}
		
		/* Generate JS structure description */
		$generator = new LivestatusJSStructureGenerator( $full_structure );
		$generator->generate();
		
		/* Generate autoloader */
		//$generator = new LivestatusAutoloaderGenerator( $classpaths );
		//$generator->generate();
	}
}