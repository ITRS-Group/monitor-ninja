<?php

require_once( __DIR__.'/../js_class_generator.php' );
require_once( __DIR__.'/../class_generator.php' );

require_once( 'common/ORMObjectGenerator.php' );
require_once( 'common/ORMObjectPoolGenerator.php' );
require_once( 'common/ORMRootGenerator.php' );
require_once( 'common/ORMRootPoolGenerator.php' );
require_once( 'common/ORMRootSetGenerator.php' );

require_once( 'meta/ORMWrapperGenerator.php' );

require_once( 'meta/ORMTableManifestGenerator.php' );
require_once( 'meta/ORMStructureManifestGenerator.php' );

class ORMBuilder {
	/**
	 * Generate base class
	 *
	 * @return void
	 **/
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

	/**
	 * Return a list of class names defining the generators for loaded classes
	 *
	 * Make sure the classes are loaded too
	 *
	 * @param string $name of the driver
	 * @return array of names for object generator, object set generator and
	 * 			object pool generator
	 */
	private function load_driver( $name ) {
		require_once( "driver_".$name."/ORM".$name."ObjectGenerator.php");
		require_once( "driver_".$name."/ORM".$name."ObjectSetGenerator.php");
		require_once( "driver_".$name."/ORM".$name."ObjectPoolGenerator.php");
		return array(
			"ORM".$name."ObjectGenerator",
			"ORM".$name."ObjectSetGenerator",
			"ORM".$name."ObjectPoolGenerator",
		);

	}

	/**
	 * Generate table
	 *
	 * @return void
	 **/
	public function generate_table( $name, $full_structure ) {
		$structure = $full_structure[$name];
		list($objgenclass, $setgenclass, $poolgenclass) = $this->load_driver($structure['source']);

		/* Generate base class */
		$generator = new $objgenclass( $name, $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class'], false, $path );
		if( !$generator->exists() )
			$generator->generate();

		/* Generate base set class */
		$generator = new $setgenclass( $name, $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate set wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class']."Set", false, $path );
		if( !$generator->exists() )
			$generator->generate();

		/* Generate base pool class */
		$generator = new $poolgenclass( $name, $full_structure );
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate pool wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class']."Pool", false, $path );
		if( !$generator->exists() )
			$generator->generate();
	}

	/**
	 * Generate manifest
	 *
	 * @return void
	 **/
	public function generate_manifest( $full_structure ) {
		/* Generate Table classes description */
		$generator = new ORMTableManifestGenerator( $full_structure );
		$generator->generate();

		/* Generate Table structure description */
		$generator = new ORMStructureManifestGenerator( $full_structure );
		$generator->generate();
	}
}