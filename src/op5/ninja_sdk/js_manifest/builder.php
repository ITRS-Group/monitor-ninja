<?php

require_once(__DIR__.'/../js_class_generator.php');

class js_manifest_writer extends js_class_generator {
	protected $content;

	public function __construct($content) {
		$this->content = $content;
		$this->classname = 'ninja_manifest';
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);

		$this->write('if(typeof(ninja_manifest) === "undefined") {');
		$this->write(    'ninja_manifest = {}', array());
		$this->write('}');

		$this->write('$.extend(true, ninja_manifest, %s);', $this->content);
	}
}

class js_manifest_Builder implements builder_interface {
	protected function load_manifest($file) {
		$manifest = array();
		require($file);
		return $manifest;
	}

	public function generate ($mod_path, $src_path) {
		$manifestdir = $mod_path . '/manifest';
		if(!is_dir($manifestdir))
			return;

		$manifests = array();
		foreach(scandir($manifestdir) as $manifest_file) {
			if($manifest_file[0] == '.')
				continue;

			$manifest = basename($manifest_file, '.php');

			$manifests[$manifest] = $this->load_manifest($mod_path.'/manifest/'.$manifest_file);
		}

		$writer = new js_manifest_writer($manifests);
		$writer->set_moduledir($mod_path);
		echo "Generating JS manifest ".$writer->get_filename()."\n";
		$writer->generate();
	}

	public function get_dependencies() {
		return array('parsegen', 'orm', 'doctags');
	}
	public function get_run_always() {
		return true;
	}
}