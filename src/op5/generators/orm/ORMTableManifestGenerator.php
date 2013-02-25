<?php

class ORMTableManifestGenerator extends class_generator {

	private $full_structure;

	public function __construct( $full_structure ) {
		$this->full_structure = $full_structure;
		$this->classname = "orm_table_classes";
		$this->set_manifest();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		
		foreach( $this->full_structure as $name => $structure ) {
			$this->write('$manifest[%s] = %s;', $name, array(
				'object' => $structure['class'].self::$model_suffix,
				'set' => $structure['class'].'Set'.self::$model_suffix,
				'pool' => $structure['class'].'Pool'.self::$model_suffix,
				));
		}
	}
}
