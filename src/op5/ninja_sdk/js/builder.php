<?php

class js_Builder implements builder_interface {
	public function generate ($mod_path, $src_path) {

		$target_path =  $mod_path . '/media/js/bundle.js';
		$files = scandir($src_path);

		sort($files);

		$js_files = array_filter($files, function ($file) {
			return preg_match("/\.js$/", $file) ? true : false;
		});

		$js_files = array_map(function ($file) use ($src_path) {
			return $src_path . '/' . $file;
		}, $js_files);


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

		fclose($target);

	}

	public function get_dependencies() {
		return array('parsegen');
	}
}
