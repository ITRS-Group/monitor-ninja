<?php

require_once( '../buildlib.php' );

require_once( 'LivestatusBaseClassGenerator.php' );
require_once( 'LivestatusBaseRootClassGenerator.php' );
require_once( 'LivestatusBasePoolClassGenerator.php' );
require_once( 'LivestatusBaseRootPoolClassGenerator.php' );

require_once( 'LivestatusBaseSetClassGenerator.php' );

require_once( 'LivestatusBaseRootSetClassGenerator.php' );

require_once( 'LivestatusWrapperClassGenerator.php' );
require_once( 'LivestatusJSStructureGenerator.php' );
require_once( 'LivestatusAutoloaderGenerator.php' );

require_once( 'LivestatusStructure.php' );

class orm_generator extends generator_module {
	protected function do_run() {

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

		$sources = array();
		foreach( $full_structure as $name => $structure ) {
			$sources[$structure['source']] = 1;
		}
		$sources = array_keys($sources);

		/* Generate base root class */
		$generator = new LivestatusBaseRootClassGenerator( 'root', $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$classpaths[$generator->get_classname()] = $generator->get_include_path();
			
		/* Generate base pool class */
		$generator = new LivestatusBaseRootPoolClassGenerator( 'root', $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$classpaths[$generator->get_classname()] = $generator->get_include_path();

		/* Generate base set class */
		$generator = new LivestatusBaseRootSetClassGenerator( 'root', $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$classpaths[$generator->get_classname()] = $generator->get_include_path();

		foreach( $sources as $source ) {
			/* Generate base Livestatus set class */
			$classname = "LivestatusBaseRoot".$source ."SetClassGenerator";
			require_once( "$classname.php" );
			$generator = new $classname( 'root', $full_structure );
			$generator->set_class_dir('base');
			$generator->generate();
			$classpaths[$generator->get_classname()] = $generator->get_include_path();
		}

		foreach( $full_structure as $name => $structure ) {
			/* Generate base class */
			$generator = new LivestatusBaseClassGenerator( $name, $full_structure );
			$generator->set_class_dir('base');
			$generator->generate();
			$classpaths[$generator->get_classname()] = $generator->get_include_path();

			/* Generate base pool class */
			$generator = new LivestatusBasePoolClassGenerator( $name, $full_structure );
			$generator->set_class_dir('base');
			$generator->generate();
			$classpaths[$generator->get_classname()] = $generator->get_include_path();

			/* Generate base set class */
			$generator = new LivestatusBaseSetClassGenerator( $name, $full_structure );
			$generator->set_class_dir('base');
			$generator->generate();
			$classpaths[$generator->get_classname()] = $generator->get_include_path();
		}
		
		$base_structure = array('class'=>'Object', 'modifiers'=>array('abstract'));

		/* Generate root class wrapper */
		$generator = new LivestatusWrapperClassGenerator( 'root', $base_structure, "%s", $classpaths );
		if( !$generator->exists() )
			$generator->generate();
			
		/* Generate pool class wrapper if not exists */
		$generator = new LivestatusWrapperClassGenerator( 'root', $base_structure, "%sPool", $classpaths );
		if( !$generator->exists() )
			$generator->generate();

		/* Generate set class wrapper if not exists */
		$generator = new LivestatusWrapperClassGenerator( 'root', $base_structure, "%sSet", $classpaths );
		if( !$generator->exists() )
			$generator->generate();

		foreach( $sources as $source ) {
			/* Generate set class wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( 'root', $base_structure, "%s".$source."Set", $classpaths );
			if( !$generator->exists() )
				$generator->generate();
		}

		foreach( $full_structure as $name => $structure ) {
			/* Generate wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( $name, $structure, "%s", $classpaths );
			if( !$generator->exists() )
				$generator->generate();

			/* Generate pool wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( $name, $structure, "%sPool", $classpaths );
			if( !$generator->exists() )
				$generator->generate();

			/* Generate set wrapper if not exists */
			$generator = new LivestatusWrapperClassGenerator( $name, $structure, "%sSet", $classpaths );
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

$generator = new orm_generator('orm');
$generator->run();
