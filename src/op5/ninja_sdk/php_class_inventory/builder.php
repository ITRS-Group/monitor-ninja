<?php
require_once (__DIR__ . "/../generator_lib.php");
require_once (__DIR__ . "/../class_generator.php");
require_once (__DIR__ . "/../php_miner.php");
require_once (__DIR__ . "/../builder_interface.php");

class php_class_inventory_generator extends class_generator {
	protected $classfiles;

	public function __construct($classfiles) {
		$this->classfiles = $classfiles;
		$this->classname = 'classes';
	}

	public function generate( $skip_generated_note = false ) {
		parent::generate($skip_generated_note);

		$this->write('return %s;', $this->classfiles);

	}
}

class php_class_inventory_Builder implements builder_interface {
	public function generate($moduledir, $confdir) {
		print "Building class index for $moduledir\n";

		$directory = new RecursiveDirectoryIterator($moduledir);
		$iterator = new RecursiveIteratorIterator($directory);
		$files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

		$classfiles = array();
		foreach ( $files as $file ) {
			$relpath = substr($file[0], strlen($moduledir)+1);
			$classes = $this->get_classes($file[0]);
			foreach($classes as $class) {
				$classfiles[strtolower($class)] = $relpath;
			}
		}

		ksort($classfiles);

		$generator = new php_class_inventory_generator($classfiles);
		$generator->set_moduledir($moduledir);
		$generator->generate();
		return $classfiles;
	}

	/**
	 * Get classes and interfaces.
	 *
	 * In the sense of an autoloader, classes and interfaces are the same
	 */
	private function get_classes($filename) {
		$file = php_miner_file::parse_file( $filename );
		if ($file === false) {
			return array();
		}

		$tags = array ();
		$class_stmts = $file->extract( 'php_miner_statement_class', true );
		$if_stmts = $file->extract( 'php_miner_statement_interface', true );

		$classes = array();
		/* @var $class_stmts php_miner_statement_class[] */
		foreach ( $class_stmts as $class_stmt ) {
			$classes[] = $class_stmt->name;
		}
		/* @var $if_stmts php_miner_statement_interface[] */
		foreach ( $if_stmts as $if_stmt ) {
			$classes[] = $if_stmt->name;
		}
		return $classes;
	}

	public function get_dependencies() {
		return array();
	}
	public function get_run_always() {
		return true;
	}
}
