<?php
class Ninja_Builder {
	protected $build_targets = array ();
	protected $builders_basedir = __DIR__;
	protected $all_targets = array();
	protected $builders = array();

	public function __construct() {
		$this->builders = array();
		foreach ( scandir( $this->builders_basedir ) as $target ) {
			if( $target[0] === '.' )
				continue;
			if( !is_dir($this->builders_basedir . DIRECTORY_SEPARATOR . $target) )
				continue;

			$builderclass = $target . "_Builder";
			require_once ($this->builders_basedir . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . "builder.php");
			$this->builders[$target] = new $builderclass();
		}

		$this->all_targets = array();
		foreach( $this->builders as $target => $builder ) {
			$this->add_target($target);
		}
	}

	/**
	 * Add a target to the all_targets list, with all dependencies met prior in the list
	 */
	protected function add_target($target) {
		/* Do we already have the target? Don't add */
		if(in_array($target, $this->all_targets))
			return;

		/* All dependencies needs to be added first */
		foreach( $this->builders[$target]->get_dependencies() as $dependency ) {
			$this->add_target($dependency);
		}

		/* At last, add the target, since everything is met */
		$this->all_targets[] = $target;
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
		/* Get a list of all affected modules */
		$affected_modules = array();
		foreach ( $this->all_targets as $target ) {
			if(isset($this->build_targets[$target])) {
				$affected_modules += array_keys($this->build_targets[$target]);
			}
		}

		/* Add build-always-targets to all affected modules */
		foreach( $this->builders as $target => $builder) {
			/* @var $builder builder_interface */
			if($builder->get_run_always()) {
				foreach($affected_modules as $moddir) {
					/*
					 * This changes the state of the class, but doesn't affect
					 * functionality.
					 *
					 * The changes however are not anything that affects
					 * functionality, since the changes is only used below in
					 * this method.
					 *
					 * Should probably be cleaned up sometime
					 */
					$this->add_module($moddir, $target);
				}
			}
		}

		foreach ( $this->all_targets as $target ) {
			print("\n\n##### Building target: $target\n\n");
			$builder = $this->builders[$target];
			$modules = isset($this->build_targets[$target]) ? $this->build_targets[$target] : array();
			foreach ( $modules as $moduledir => $confdir ) {
				if( !$builder->get_run_always() && !is_dir($moduledir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $target) )
					continue;
				$builder->generate( $moduledir, $confdir );
			}
		}
	}
}
