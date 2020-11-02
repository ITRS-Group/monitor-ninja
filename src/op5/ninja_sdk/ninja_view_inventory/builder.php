<?php
require_once (__DIR__ . "/../generator_lib.php");
require_once (__DIR__ . "/../class_generator.php");
require_once (__DIR__ . "/../builder_interface.php");

class ninja_view_inventory_generator extends class_generator {
	protected $viewfiles;

	public function __construct($viewfiles) {
		$this->viewfiles = $viewfiles;
		$this->classname = 'views';
	}

	public function generate( $skip_generated_note = false ) {
		parent::generate($skip_generated_note);
		$this->write('return %s;', $this->viewfiles);
	}
}

class ninja_view_inventory_Builder implements builder_interface {
	public function generate($moduledir, $confdir) {
		$viewdir = $moduledir . '/views';
		if(!is_dir($viewdir))
			return array();

		print "Building view index for $moduledir\n";

		$directory = new RecursiveDirectoryIterator($viewdir);
		$iterator = new RecursiveIteratorIterator($directory);
		$files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

		$viewfiles = array();
		foreach ( $files as $file ) {
			$relpath = substr($file[0], strlen($viewdir)+1);
			$viewname = substr($relpath, 0, -4);
			$viewfiles[$viewname] = 'views/' . $relpath;
		}

		ksort($viewfiles);

		$generator = new ninja_view_inventory_generator($viewfiles);
		$generator->set_moduledir($moduledir);
		$generator->generate();
		return $viewfiles;
	}

	public function get_dependencies() {
		return array();
	}
	public function get_run_always() {
		return true;
	}
}
