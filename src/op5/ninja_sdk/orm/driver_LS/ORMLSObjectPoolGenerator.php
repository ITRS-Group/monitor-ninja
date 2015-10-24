<?php

require_once(__DIR__."/../common/ORMObjectPoolGenerator.php");

class ORMLSObjectPoolGenerator extends ORMObjectPoolGenerator {
	public function generate_backend_specific_functions() {
		$this->generate_map_name_to_backend();
	}

	/**
	 * Generate the method map_name_to_backend for the object set
	 *
	 * @param $oset ORMObjectSetGenerator
	 */
	public function generate_map_name_to_backend() {
		$this->init_function('map_name_to_backend', array('name', 'prefix'), array('static'), array('prefix' => false));
		$this->write('if($prefix === false) {');
		$this->write('$prefix = "";');
		$this->write('}');
		foreach($this->structure['structure'] as $field => $type ) {
			$backend_field = $field;
			if(isset($this->structure['rename']) && isset($this->structure['rename'][$field])) {
				$backend_field = $this->structure['rename'][$field];
			}
			if(is_array($type)) {
				$subobjpool_class = $type[0].'Pool'.self::$model_suffix;
				$this->write('if(substr($name,0,%s) == %s) {', strlen($field)+1, $field.'.');
				$this->write('return '.$subobjpool_class.'::map_name_to_backend(substr($name,%d),%s);', strlen($field)+1, $type[1]);
				$this->write('}');
			} else {
				$this->write('if($name == %s) {', $field);
				$this->write('return $prefix.%s;',$backend_field);
				$this->write('}');
			}
		}
		$this->write('return false;');
		$this->write('}');
	}
}
