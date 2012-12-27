<?php

class LivestatusBaseSetClassGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	private $associations;
	
	public function __construct( $name, $structure ) {
		$this->name = $name;
		$this->structure = $structure[$name];
		$this->objectclass = $this->structure['class'].self::$model_suffix;
		$this->classname = 'Base'.$this->structure['class'].'Set';

		$this->associations = array();
		
		foreach( $structure as $table => $tbl_struct ) {
			foreach( $tbl_struct['structure'] as $name => $type ) {
				if( is_array( $type ) ) {
					if( $type[0] == $this->structure['class'] ) {
						$this->associations[] = array(
								$table,
								$tbl_struct['class'],
								substr( $type[1], 0, -1 ) // Drop last _
						);
					}
				}
			}
		}
		
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( 'Object'.$this->structure['source'].'Set', array('abstract') );
		$this->variable('table',$this->name,'protected');
		$this->variable('class',$this->structure['class'].self::$model_suffix,'protected');
		$this->generate_validate_columns();
		foreach( $this->associations as $assoc ) {
			$this->generate_association_get_set( $assoc[0], $assoc[1], $assoc[2] );
		}
		$this->finish_class();
	}
	
	public function generate_validate_columns() {
		$this->init_function('validate_columns', array('columns'));
		foreach($this->structure['structure'] as $name => $type ) {
			if( is_array($type) ) {
				$this->write('$subcolumns = array();');
				$this->write('$tmpcolumns = array();');
				$this->write('foreach( $columns as $col ) {');
				$this->write('if(substr($col,0,%s) == %s) {', strlen($name)+1,$name.'.');
				$this->write('$subcolumns[] = substr($col,%s);', strlen($name)+1);
				$this->write('} else {');
				$this->write('$tmpcolumns[] = $col;');
				$this->write('}');
				$this->write('}');
				$this->write('$columns = $tmpcolumns;');
				$this->write('$tmpset = new '.$type[0].'Set'.self::$model_suffix.'();');
				$this->write('$subcolumns = $tmpset->validate_columns($subcolumns);');
				$this->write('if($subcolumns === false) return false;');
				$this->write('foreach($subcolumns as $col) {');
				$this->write('$columns[] = %s.$col;', $name.'.');
				$this->write('}');
			}
		}
		foreach($this->structure['key'] as $keypart ) {
			$this->write('if( !in_array(%s, $columns) ) $columns[] = %s;', $keypart, $keypart);
		}
		$this->write('return $columns;');
		$this->finish_function();
	}
	
	private function generate_association_get_set($table, $class, $field) {
		$this->init_function('get_'.$table);
		$this->write('$result = '.$class.'Pool'.self::$model_suffix.'::all();');
		$this->write('$result->filter = $this->filter->prefix(%s);', $field.'.');
		$this->write('return $result;');
		$this->finish_function();
	}
}