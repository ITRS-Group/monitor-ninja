<?php

require_once(__DIR__."/../common/types/ORMType.php");

class ORMStructureManifestGenerator extends class_generator {
	private $full_structure = array();

	public function __construct ($full_structure) {
		$this->full_structure = $full_structure;
		$this->classname = "orm_structure";
		$this->set_manifest();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);

		foreach ($this->full_structure as $table => $struct) {
			$tblresult = array();
			foreach ($struct['structure'] as $field => $type) {
				$ormtype = ORMTypeFactory::factory($field, $type, $struct['structure']);
				if (is_a($ormtype, "ORMTypeLSRelation")) {
					$tblresult[$field] = array('object', $this->lookup_class($type[0]));
				} else {
					$tblresult[$field] = array($type);
				}
			}
			$result[$table] = $tblresult;
			$this->write('$manifest[%s] = %s;', $table, $tblresult);
		}
	}

	private function lookup_class($class) {
		foreach ($this->full_structure as $table => $struct) {
			if ($struct['class'] == $class) {
				return $table;
			}
		}
		return false;
	}
}
