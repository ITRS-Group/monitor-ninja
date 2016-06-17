<?php

require_once(__DIR__.'/../class_generator.php');

class js_loader_hook_generator extends class_generator {
	protected $bundle_path;

	public function __construct($bundle_path) {
		$this->bundle_path = $bundle_path;
		$this->classname = 'load_js_bundles';
		$this->set_basedir('hooks');
	}

	public function generate( $skip_generated_note = false ) {
		parent::generate($skip_generated_note);

		$this->write('$module_path = Kohana::$module_path;');
		$this->write('Event::add("system.post_controller_constructor", function () use ($module_path) {');
		$this->write(    '$controller = Event::$data;');
		$this->write(    '$controller->template->js[] = $module_path.%s;', $this->bundle_path);
		$this->write('});');
	}
}

class js_Builder implements builder_interface {
	public function generate ($mod_path, $src_path) {
		if(!is_dir($src_path))
			return;

		$bundle_path = '/media/js/bundle_'.time().'.js';

		$target_dir =  $mod_path . '/media/js';
		$target_path =  $mod_path . $bundle_path;
		$hook_path =  $mod_path . '/hooks/load_js_bundles.php';

		$directory = new RecursiveDirectoryIterator($src_path);
		$iterator = new RecursiveIteratorIterator($directory);
		$files = new RegexIterator($iterator, '/^.+\.js$/i', RecursiveRegexIterator::GET_MATCH);

		$js_files = array();
		foreach ($files as $file) {
			$js_files[] = $file[0];
		}

		/*
		 * If there is no js files, we don't need to bundle
		 */
		if(count($js_files) == 0)
			return;

		sort($js_files);
		echo "Bundling: " . $mod_path . "\n";

		if(!is_dir($target_dir) && !mkdir($target_dir, 0755, true))
			throw new GeneratorException( "Could not create dir $target_dir" );

		$target = fopen($target_path, 'w');

		foreach ($js_files as $file) {
			echo " - Adding " . $file . "\n";
			fwrite($target, <<<EOF

/*******************
 * From src: $file
 *******************/

EOF
);
			fwrite($target, file_get_contents($file));
			fwrite($target, "\n\n");
		}

		echo "   -> " . $target_path . "\n";

		fclose($target);
		file_put_contents($hook_path, <<<EOF
<?php
Event::add('system.post_controller_constructor', function () {
    \$controller = Event::\$data;
	\$controller->template->js[] = "$target_path";
});
EOF
);

		$hookgen = new js_loader_hook_generator($bundle_path);
		$hookgen->set_moduledir($mod_path);
		$hookgen->generate();

	}

	public function get_dependencies() {
		return array('parsegen', 'js_manifest');
	}
	public function get_run_always() {
		return true;
	}
}
