<?php

require_once( 'ORMObjectGenerator.php' );
require_once( 'ORMObjectPoolGenerator.php' );
require_once( 'ORMObjectSetGenerator.php' );
require_once( 'ORMRootGenerator.php' );
require_once( 'ORMRootPoolGenerator.php' );
require_once( 'ORMRootSetGenerator.php' );

require_once( 'ORMWrapperGenerator.php' );

require_once( 'ORMLSSetGenerator.php' );
require_once( 'ORMSQLSetGenerator.php' );

require_once( 'ORMManifestGenerator.php' );
require_once( 'ORMJSStructureGenerator.php' );

class ORMBuilder {
	public function generate_base() {
		/* Generate base root class */
		$generator = new ORMRootGenerator();
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();
		
		/* Generate root class wrapper */
		$generator = new ORMWrapperGenerator( 'Object', false, $path );
		if( !$generator->exists() )
			$generator->generate();
			
		
		
		/* Generate base pool class */
		$generator = new ORMRootPoolGenerator();
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate pool class wrapper if not exists */
		$generator = new ORMWrapperGenerator( 'ObjectPool', false, $path );
		if( !$generator->exists() )
			$generator->generate();
		
		
		
		/* Generate base set class */
		$generator = new ORMRootSetGenerator();
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();
		
		/* Generate set class wrapper if not exists */
		$generator = new ORMWrapperGenerator( 'ObjectSet', false, $path );
		if( !$generator->exists() )
			$generator->generate();
	}

	public function generate_source( $source ) {
		$classname = "ORM".$source ."SetGenerator";
		require_once( "$classname.php" );
		$generator = new $classname( 'root', $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();
		
		/* Generate set class wrapper if not exists */
		$generator = new ORMWrapperGenerator( "Object".$source."Set", array('abstract'), $path );
		if( !$generator->exists() )
			$generator->generate();
	}

	public function generate_table( $name, $full_structure ) {
		$structure = $full_structure[$name];
		
		/* Generate base class */
		$generator = new ORMObjectGenerator( $name, $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class'], false, $path );
		if( !$generator->exists() )
			$generator->generate();
		
		
		
		/* Generate base pool class */
		$generator = new ORMObjectPoolGenerator( $name, $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate pool wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class']."Pool", false, $path );
		if( !$generator->exists() )
			$generator->generate();
		
		
		/* Generate base set class */
		$generator = new ORMObjectSetGenerator( $name, $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();
		
		/* Generate set wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class']."Set", false, $path );
		if( !$generator->exists() )
			$generator->generate();
	}
	
	public function generate_js_structure( $full_structure ) {
		/* Generate JS structure description */
		$generator = new ORMJSStructureGenerator( $full_structure );
		$generator->generate();
	}
	
	
	public function generate_manifest( $full_structure ) {
		/* Generate JS structure description */
		$generator = new ORMManifestGenerator( $full_structure );
		$generator->generate();
	}
}