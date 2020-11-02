<?php

require_once('ORMGenerator.php');
require_once('types/ORMType.php');

abstract class ORMObjectSetGenerator extends ORMGenerator {
	public $associations; /** an association is a way to get a one-to-many */

	public function __construct( $name, $full_structure ) {
		parent::__construct($name, $full_structure);
		$this->classname = 'Base'.$this->structure['class'].'Set';

		$this->associations = array();

		foreach( $full_structure as $table => $tbl_struct ) {
			foreach( $tbl_struct['structure'] as $name => $type ) {
				$ormtype = ORMTypeFactory::factory($name, $type, $tbl_struct['structure']);
				if(is_a($ormtype, 'ORMTypeLSRelation')) {
					if( $type[0] == $this->structure['class'] ) {
						$this->associations[] = array(
							$table,
							$tbl_struct['class'],
							$name
						);
					}
				}
			}
		}

		$this->parent_class = 'ObjectSet';
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( $this->parent_class, array('abstract') );

		$this->generate_common();

		$this->variable('table',$this->name,'public');

		$this->variable('key_columns',$this->structure['key'],'protected');

		$this->generate_backend_specific_functions();

		$this->generate_apply_columns_rewrite();

		/* External interface, backend specific */
		$this->generate_stats();
		$this->generate_count();
		$this->generate_it();

		if($this->writable) {
			$this->generate_update();
			$this->generate_delete();
		}

		foreach( $this->associations as $assoc ) {
			$this->generate_association_get_set( $assoc[0], $assoc[1], $assoc[2] );
		}

		$this->finish_class();
	}

	public function generate_apply_columns_rewrite() {
		$this->init_function('apply_columns_rewrite', array('columns', 'prefix'),array('static'),array('prefix'=>''));
		$this->write( 'foreach('.$this->obj_class.'::rewrite_columns() as $column => $rewrites) {');
		$this->write(   'if( in_array( $prefix.$column, $columns ) ) {' );
		$this->write(     'foreach($rewrites as $rewrite) {' );
		$this->write(       '$columns[] = $prefix.$rewrite;' );
		$this->write(     '}' );
		$this->write(   '}' );
		$this->write( '}' );
		foreach( $this->structure['structure'] as $name => $type ) {
			$ormtype = ORMTypeFactory::factory($name, $type, $this->structure);
			if(isset($this->structure['rename']) && isset($this->structure['rename'][$name])) {
				$name = $this->structure['rename'][$name];
			}
			/* Legacy relation */
			if (is_a($ormtype, 'ORMTypeLSRelation')) {
				$this->write('$columns = '.$type[0].'Set'.self::$model_suffix.'::apply_columns_rewrite($columns,%s);',$name.".");
			}
		}
		$this->write('return $columns;');
		$this->finish_function();
	}

	public function generate_association_get_set($table, $class, $field) {
		$this->init_function('get_'.$table);
		$this->write('$result = '.$class.'Pool'.self::$model_suffix.'::all();');
		$this->write('$result->filter = $this->filter->prefix(%s);', $field.'.');
		$this->write('return $result;');
		$this->finish_function();
	}

	public function generate_it() {
		$this->init_function( 'it', array('columns','order','limit','offset'), array(), array('order' => array(), 'limit'=>false, 'offset'=>false) );
		$this->write(
			'return ' . $this->pool_class .
				 '::pool()->it($this->get_auth_filter(),$columns,$order,$limit,$offset);');
		$this->finish_function();
	}

	public function generate_update() {
		$this->init_function( 'update', array('values') );
		$this->write(
			'return ' . $this->pool_class .
			'::pool()->update($this->get_auth_filter(),$values);');
		$this->finish_function();
	}
	public function generate_delete() {
		$this->init_function( 'delete' );
		$this->write(
			'return ' . $this->pool_class .
				 '::pool()->delete($this->get_auth_filter());');
		$this->finish_function();
	}

	public function generate_count() {
		$this->init_function('count', array());
		$this->write('return ' . $this->pool_class . '::pool()->count($this->get_auth_filter());');
		$this->finish_function();
	}

	public function generate_stats() {
		$this->init_function('stats', array ('intersections'));
		$this->write(
			'return ' . $this->pool_class .
				 '::pool()->stats($this->get_auth_filter(),$intersections);');
		$this->finish_function();
	}

	abstract public function generate_backend_specific_functions();
}
