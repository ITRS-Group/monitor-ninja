<?php
class Ninja_Builder {
	protected $build_targets = array ();
	protected $builders_basedir = __DIR__;
	protected $all_targets = array();
	protected $builders = array();

	public function __construct() {
		$this->all_targets = array();
		foreach( scandir( $this->builders_basedir ) as $target ) {
			if( $target[0] === '.' )
				continue;
			if( !is_dir($this->builders_basedir . DIRECTORY_SEPARATOR . $target) )
				continue;
			$this->all_targets[] = $target;
		}

		$this->builders = array();
		foreach ( $this->all_targets as $target ) {
			$builderclass = $target . "_Builder";
			require_once ($this->builders_basedir . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . "builder.php");
			$this->builders[$target] = new $builderclass();
		}
	}

	/**
	 * Add a module to the builder
	 * For external modules, this can be executed once, to build a single
	 * module:
	 * $builder = new Ninja_Builder();
	 * $builder->add_module("my_module");
	 * $builder->generate();
	 * For ninja internally, add all modules, and then call generate.
	 *
	 * @param string $moduledir
	 */
	public function add_module($moduledir = null, $target = null) {
		$srcdir = $moduledir . DIRECTORY_SEPARATOR . "src";
		if (! is_dir( $srcdir ))
			return;

		/* If a target is specified, use that, otherwise list */
		if(empty($target)) {
			$targets = $this->all_targets;
		} else {
			$targets = array( $target );
		}

		/* Register all targets for this module */
		foreach ( $targets as $target ) {
			if (! array_key_exists( $target, $this->build_targets ))
				$this->build_targets[$target] = array ();
			$this->build_targets[$target][$moduledir] = $srcdir . DIRECTORY_SEPARATOR . $target;
		}
	}

	/**
	 * Generates all modules added by add_module earlier.
	 * This should only be called once. Destroy the object after this method is
	 * called.
	 */
	public function generate() {
		foreach ( $this->all_targets as $target ) {
			$builder = $this->builders[$target];
			$modules = isset($this->build_targets[$target]) ? $this->build_targets[$target] : array();
			foreach ( $modules as $moduledir => $confdir ) {
				if( !is_dir($moduledir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $target) )
					continue;
				$builder->generate( $moduledir, $confdir );
			}
		}
	}
}
