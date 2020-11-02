<?php

require_once('ORMGenerator.php');
require_once('types/ORMType.php');

abstract class ORMObjectGenerator extends ORMGenerator {
	protected $associations;

	/**
	 * Construct
	 *
	 * @return void
	 **/
	public function __construct( $name, $full_structure ) {
		parent::__construct($name, $full_structure);
		$this->classname = 'Base'.$this->structure['class'];

		$this->associations = array();

		/**
		 * Legacy implicit associations of LS Relations
		 *
		 * Used to generate get_<LSRelation Type>_set where a foreign table mentions the
		 * currently generated class as a relation.
		 *
		 * e.g.
		 * 	downtimes has the field:
		 * 		'service' => array('Service', 'service_')
		 * 	so when generating the Service class it will generate a:
		 * 		get_downtimes_set()
		 * 	which will retrieve all downtimes related to that service.
		 *
		 * @TODO Replace with explicit ORMTypeSet
		 * 	e.g.
		 * 		In the service structure for the same effect:
		 * 			'downtimes' => array('set', array('filters' => array(
		 *				array('service.host.name', 'host.name', '='),
		 *				array('service.description', 'description', '=')
		 * 			)))
		 *
		 */
		foreach( $this->full_structure as $table => $tbl_struct ) {
			foreach( $tbl_struct['structure'] as $field => $type ) {
				$ormtype = ORMTypeFactory::factory($field, $type, $tbl_struct['structure']);
				if (is_a($ormtype, 'ORMTypeLSRelation')) {
					if ($type[0] == $this->structure['class']) {
						$this->associations[] = array(
							$table,
							$tbl_struct['class'],
							substr( $type[1], 0, -1 ) // Drop last _
						);
					}
				}
			}
		}

		$this->parent_class = 'Object';
		if(isset($this->structure['object_custom_parent'])) {
			$this->parent_class = $this->structure['object_custom_parent'];
		}
	}

	/**
	 * Generate
	 *
	 * @return void
	 **/
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( $this->parent_class, array('abstract') );
		if($this->writable) {
			$rename = isset($this->structure['rename']) ? $this->structure['rename'] : array();
			$this->variable( '_changed', array(), 'protected' );
			$this->variable( '_renamed', $rename, 'protected' );
			$this->variable( '_oldkey', false, 'private' );
		}
		$this->write();

		$this->generate_common();

		/* Storage */
		foreach( $this->structure['structure'] as $field => $type ) {
			$type = ORMTypeFactory::factory($field, $type, $this->structure);
			$this->write( "private \$$field = " . $type->get_default_value()  . ";" );
		}

		$this->generate_factory_from_array();
		$this->generate_factory_from_setiterator();

		if($this->writable) {
			$this->generate_validate();
			$this->generate_save();
			$this->generate_delete();
		}

		$this->generate_get_key();

		/* Getters and setters */
		foreach ($this->structure['structure'] as $field => $type) {
			$type = ORMTypeFactory::factory($field, $type, $this->structure);
			$type->generate_get($this);
			if ($this->writable) {
				$type->generate_set($this);
			}
		}

		if (isset($this->structure['renderable'])) {
			$this->generate_get_renderable_fields();
		}

		foreach( $this->associations as $assoc ) {
			$this->generate_association_get_set( $assoc[0], $assoc[1], $assoc[2] );
		}

		$this->finish_class();
	}

	/**
	 * When we mock the ORM structure, we do it by defining data as
	 * PHP arrays. If we want to add hierarchical relationships, such
	 * as hosts definitions inside of service definitions, you may
	 * well do so, this method untangles the different types.
	 */
	private function generate_factory_from_array() {
		/* Make it possible to generate empty objects if writable */
		$this->init_function( "factory_from_array", array( 'values', 'export' ),  array('static'), array());

		$this->write( '$obj = new static();');
		$this->write( '$structure = array(' );
		foreach ($this->structure['structure'] as $field => $type) {
			$ormtype = ORMTypeFactory::factory($field, $type, $this->structure);
			if(!is_a($ormtype, 'ORMTypeLSRelation')) {
				// ignore the sub objects, they are handled below
				$this->write("'$field' => " . var_export($type, true) . ",");
			}
		}
		$this->write( ');' );

		$this->write( '$obj->export = array();' );
		$this->write( '$subobj_export = array();' );
		$this->write( 'if($export === false) $export = array();'); //FIXME
		$this->write( 'foreach( $export as $expcol ) {');
		/* Do not export password fields as they may contain sensitive data */
		$this->write(     'if (isset($structure[$expcol]) && $structure[$expcol] === "password") continue;' );
		$this->write(     '$parts = explode(".", $expcol, 2);');
		$this->write(     'if(count($parts) == 2) {');
		$this->write(         'if(!isset($subobj_export[$parts[0]])) {');
		$this->write(             '$subobj_export[$parts[0]] = array();');
		$this->write(         '}');
		$this->write(         '$subobj_export[$parts[0]][] = $parts[1];');
		$this->write(         '$obj->export[] = $parts[0];');
		$this->write(     '} else {');
		$this->write(         '$obj->export[] = $parts[0];');
		$this->write(     '}');
		$this->write( '}');
		$this->comment('If object fields exists, make sure the object only exists in the export array once');
		$this->write( '$obj->export = array_unique($obj->export);');
		foreach( $this->structure['structure'] as $field => $type ) {
			$type = ORMTypeFactory::factory($field, $type, $this->structure);
			$type->generate_array_set($this);
		}
		if($this->writable) {
			$this->write( '$obj->_oldkey = array();');
			foreach($this->key as $name) {
				$this->write( "\$obj->_oldkey[%s] = \$obj->$name;", $name);
			}
		}
		$this->write( 'return $obj;');
		$this->finish_function();
	}


	/**
	 * Generates a class construct
	 *
	 * @return void
	 **/
	private function generate_factory_from_setiterator() {
		/* Make it possible to generate empty objects if writable */
		$this->init_function( "factory_from_setiterator", array( 'values', 'prefix', 'export' ),  array('static'), array());

		$this->write( '$obj = new static();');
		$this->write( '$structure = array(' );
		foreach ($this->structure['structure'] as $field => $type) {
			$ormtype = ORMTypeFactory::factory($field, $type, $this->structure);
			if(!is_a($ormtype, 'ORMTypeLSRelation')) {
				// ignore the sub objects, they are handled below
				$this->write("'$field' => " . var_export($type, true) . ",");
			}
		}
		$this->write( ');' );

		$this->write( '$obj->export = array();' );
		$this->write( '$subobj_export = array();' );
		$this->write( 'if($export === false) $export = array();'); //FIXME
		$this->write( 'foreach( $export as $expcol ) {');
		/* Do not export password fields as they may contain sensitive data */
		$this->write(     'if (isset($structure[$expcol]) && $structure[$expcol] === "password") continue;' );
		$this->write(     '$parts = explode(".", $expcol, 2);');
		$this->write(     'if(count($parts) == 2) {');
		$this->write(         'if(!isset($subobj_export[$parts[0]])) {');
		$this->write(             '$subobj_export[$parts[0]] = array();');
		$this->write(         '}');
		$this->write(         '$subobj_export[$parts[0]][] = $parts[1];');
		$this->write(         '$obj->export[] = $parts[0];');
		$this->write(     '} else {');
		$this->write(         '$obj->export[] = $parts[0];');
		$this->write(     '}');
		$this->write( '}');
		$this->comment('If object fields exists, make sure the object only exists in the export array once');
		$this->write( '$obj->export = array_unique($obj->export);');
		foreach( $this->structure['structure'] as $field => $type ) {
			$type = ORMTypeFactory::factory($field, $type, $this->structure);
			$type->generate_iterator_set($this);
		}
		if($this->writable) {
			$this->write( '$obj->_oldkey = array();');
			foreach($this->key as $name) {
				$this->write( "\$obj->_oldkey[%s] = \$obj->$name;", $name);
			}
		}
		$this->write( 'return $obj;');
		$this->finish_function();
	}

	/**
	 * Generate the validate method, if object is writable
	 *
	 * The validate method returns if the object is ok to save. Default is to
	 * always allow the object to be saved, but can be overridden.
	 */
	protected function generate_validate() {
		$this->init_function('validate', array(), array('protected'));
		$this->write('return true;');
		$this->finish_function();
	}

	/**
	 * Generate the save method, if object is writable
	 */
	private function generate_save() {
		$this->init_function('save');

		$this->write('if( empty($this->_changed) ) {');
		$this->write('return;');
		$this->write('}');

		$this->write('if( !$this->validate() ) {');
		$this->write('return;');
		$this->write('}');

		$this->write('$values = array();');
		foreach( $this->structure['structure'] as $field => $type ) {
			$type = ORMTypeFactory::factory($field, $type, $this->structure);
			$this->write('if(isset($this->_changed[%s]) && $this->_changed[%s]) {', $field, $field);
			$type->generate_save($this);
			$this->write('}');
		}
		$this->write(  'if($this->_oldkey === false) {'); // New
		$this->write(     '$pool = new '.$this->pool_class.'();');
		$this->write(     '$insid = $pool->insert_single($values);');
		// Handle auto increment value, but only if single column key of type int
		if(count($this->key) == 1 && $this->structure['structure'][$this->key[0]] == 'int') {
			$this->write(     'if($insid !== false && $insid > 0) {');
			$this->write(        '$this->'.$this->key[0].' = $insid;');
			$this->write(     '}');
		}
		$this->write(  '} else {'); // Update
		$this->write(     '$set = '.$this->pool_class.'::all();');
		foreach($this->key as $name) {
			$this->write(     '$set = $set->reduce_by(%s, $this->_oldkey[%s], "=");', $name, $name);
		}
		$this->write(     '$set->update($values);');
		$this->write(  '}');
		$this->write(  '$this->_oldkey = array();');
		foreach($this->key as $name) {
			$this->write( "\$this->_oldkey[%s] = \$this->$name;", $name);
		}
		$this->write(  '$this->_changed = array();');
		$this->finish_function();
	}

	/**
	 * Generate the save method, if object is writable
	 */
	private function generate_delete() {
		$this->init_function('delete');
		$this->write(  'if($this->_oldkey === false) {'); // New
		$this->write(     'return;'); // Nothing to delete, it's not saved
		$this->write(  '}');
		$this->write(  '$set = '.$this->pool_class.'::all();');
		foreach($this->key as $name) {
			$this->write(  '$set = $set->reduce_by(%s, $this->_oldkey[%s], "=");', $name, $name);
		}
		$this->write(  '$set->delete($this->_oldkey);');
		$this->write(  '$this->_oldkey = false;');
		$this->write(  '$this->_changed = array();');
		$this->finish_function();
	}

	/**
	 * Generate association get set
	 *
	 * @param $table string
	 * @param $class string
	 * @param field string
	 * @return void
	 **/
	private function generate_association_get_set($table, $class, $field) {
		$this->init_function('get_'.$table.'_set');
		$this->write('$set = '.$class.'Pool'.self::$model_suffix.'::all();');
		foreach( $this->key as $key_field ) {
			$keys = explode(".", $key_field);
			$getter = implode("->", array_map(function ($field) {return "get_{$field}()";}, $keys));
			$this->write('$set = $set->reduce_by(%s,$this->'.$getter.',"=");', $field.'.'.$key_field);
		}
		$this->write('return $set;');
		$this->finish_function();
	}

	/**
	 * Generate get key
	 *
	 * @return void
	 **/
	private function generate_get_key() {
		$this->init_function("get_key");
		$matchline = '$key = $this%s;';
		$got = false;
		foreach( $this->key as $keypart ) {
			// Build getter sequence
			$call = "";
			foreach( explode('.',$keypart) as $part ) {
				$call .= "->get_$part()";
			}

			// Use sprintf instead of embedded in write. write escapes
			$this->write( sprintf( $matchline, $call ) );
			$got = true;
			$matchline = '$key .= ";".$this%s;';
		}
		if( $got ) {
			$this->write('return $key;');
		} else {
			$this->write('return false;');
		}
		$this->finish_function();
	}

	private function get_renderable_value ($value) {
		$render = "";
		if (is_array($value)) {
			$render .= 'array(';
			foreach ($value as $key => $value) {
				if (is_string($key)) {
					$render .= "'$key' => " . $this->get_renderable_value($value) . ',' ;
				} else {
					$render .= $this->get_renderable_value($value);
				}
			}
			$render .= '),';
		} elseif (is_string($value)) {
			return "'$value'";
		} elseif (is_int($value) || is_float($value)) {
			return $value;
		} elseif (is_bool($value)) {
			return ($value) ? 'true' : 'false';
		}
		return $render;
	}

	private function generate_get_renderable_fields () {

		$this->init_function("get_renderable_fields", array());
		$this->write("return array(");

		foreach ($this->structure['renderable'] as $id => $field) {
			/**
			 * If this field is a key for the object, mark it as
			 * such so that we may render it as non-editable
			 */
			if (in_array($id, $this->structure['key'], true))
				$field["key"] = true;
			/**
			 * Does the structure field for this select input allow
			 * multiple values, if so then it is a multiselect
			 */
			if ($field['type'] === 'select' && $this->structure['structure'][$id] === 'list')
				$field["multiple"] = "multiple";
			$this->write("'$id' => " . $this->get_renderable_value($field));
		}
		$this->write( ");" );
		$this->finish_function();
	}

}
