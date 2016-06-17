<?php

class js_Builder implements builder_interface {
	public function generate ($mod_path, $src_path) {

		$target_dir =  $mod_path . '/media/js';
		$target_path =  $mod_path . '/media/js/bundle_'.time().'.js';
		$hook_path =  $mod_path . '/hooks/load_js_bundles.php';

		$directory = new RecursiveDirectoryIterator($src_path);
		$iterator = new RecursiveIteratorIterator($directory);
		$files = new RegexIterator($iterator, '/^.+\.js$/i', RecursiveRegexIterator::GET_MATCH);

		$js_files = array();
		foreach ($files as $file) {
			$js_files[] = $file[0];
		}

		sort($js_files);
		echo "Bundling: " . $mod_path . "\n";
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

		if(!is_dir($target_dir) && !mkdir($target_dir, 0755, true))
			throw new GeneratorException( "Could not create dir $target_dir" );

		fclose($target);
		file_put_contents($hook_path, <<<EOF
<?php
Event::add('system.post_controller_constructor', function () {
    \$controller = Event::\$data;
	\$controller->template->js[] = "$target_path";
});
EOF
);

	}

	public function get_dependencies() {
		return array('parsegen');
	}
	public function get_run_always() {
		return true;
	}
}
