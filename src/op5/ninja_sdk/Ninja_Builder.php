<?php
class Ninja_Builder {
	protected $build_targets = array ();
	protected $builders_basedir = __DIR__;

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
	public function add_module($moduledir) {
		$srcdir = $moduledir . DIRECTORY_SEPARATOR . "src";
		if (! is_dir( $srcdir ))
			return;
		foreach ( scandir( $srcdir ) as $target ) {
			if ($target[0] == '.')
				continue;
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
		$builders = array ();

		foreach ( $this->build_targets as $target => $modules ) {
			$builderclass = $target . "_Builder";
			require_once ($this->builders_basedir . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . "builder.php");
			$builders[$target] = new $builderclass();
		}

		foreach ( $this->build_targets as $target => $modules ) {
			$builder = $builders[$target];
			foreach ( $modules as $moduledir => $confdir ) {
				$builder->generate( $moduledir, $confdir );
			}
		}
	}
}