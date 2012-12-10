<?php

class LivestatusBaseSetClassGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	
	public function __construct( $name, $descr ) {
		$this->name = $name;
		$this->structure = $descr;
		$this->objectclass = $descr['class'].self::$model_suffix;
		$this->classname = 'Base'.$descr['class'].'Set';
		$this->set_model();
	}
	
	public function generate() {
		parent::generate();
		$this->init_class( 'ObjectSet', array('abstract') );
		$this->variable('table',$this->name,'protected');
		$this->variable('class',$this->structure['class'].self::$model_suffix,'protected');
		$this->generate_validate_columns();
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
		$this->write('return $columns;');
		$this->finish_function();
	}
}