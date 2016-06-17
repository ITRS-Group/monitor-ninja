<?php
require_once(__DIR__."/../generator_lib.php");

require_once( __DIR__.'/../js_class_generator.php' );
require_once( __DIR__.'/../class_generator.php' );

require_once( __DIR__.'/common/ORMObjectGenerator.php' );
require_once( __DIR__.'/common/ORMObjectPoolGenerator.php' );

require_once( __DIR__.'/meta/ORMWrapperGenerator.php' );

require_once( __DIR__.'/meta/ORMTableManifestGenerator.php' );
require_once( __DIR__.'/meta/ORMStructureManifestGenerator.php' );

class orm_Builder implements builder_interface {
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
	public function generate_table( $name, $full_structure, $moduledir = false ) {
		$structure = $full_structure[$name];
		list($objgenclass, $setgenclass, $poolgenclass) = $this->load_driver($structure['source']);

		/* Generate base class */
		$generator = new $objgenclass( $name, $full_structure );
		/* @var $generator ORMGenerator */
		$generator->set_moduledir($moduledir);
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class'], false, $path );
		$generator->set_moduledir($moduledir);
		if( !$generator->exists() )
			$generator->generate();

		/* Generate base set class */
		$generator = new $setgenclass( $name, $full_structure );
		/* @var $generator ORMGenerator */
		$generator->set_moduledir($moduledir);
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate set wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class']."Set", false, $path );
		$generator->set_moduledir($moduledir);
		if( !$generator->exists() )
			$generator->generate();

		/* Generate base pool class */
		$generator = new $poolgenclass( $name, $full_structure );
		/* @var $generator ORMGenerator */
		$generator->set_moduledir($moduledir);
		$generator->set_class_dir('base');
		$generator->generate();
		$path = $generator->get_include_path();

		/* Generate pool wrapper if not exists */
		$generator = new ORMWrapperGenerator( $structure['class']."Pool", false, $path );
		$generator->set_moduledir($moduledir);
		if( !$generator->exists() )
			$generator->generate();
	}

	/**
	 * Generate manifest
	 *
	 * @return void
	 **/
	public function generate_manifest( $full_structure, $moduledir ) {
		/* Generate Table classes description */
		$generator = new ORMTableManifestGenerator( $full_structure );
		$generator->set_moduledir($moduledir);
		$generator->generate();

		/* Generate Table structure description */
		$generator = new ORMStructureManifestGenerator( $full_structure );
		$generator->set_moduledir($moduledir);
		$generator->generate();
	}

	public function generate($moduledir, $confdir) {
		printf("Generating ORM for %s\n", $moduledir);
		$tables = array();
		include("$confdir/structure.php");
		foreach($tables as $table => $structure) {
			$this->generate_table($table, $tables, $moduledir);
		}
		$this->generate_manifest($tables, $moduledir);
	}

	public function get_dependencies() {
		return array();
	}
	public function get_run_always() {
		return false;
	}
}