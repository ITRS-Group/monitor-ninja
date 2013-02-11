<?php

class ORMJSStructureGenerator extends js_class_generator {
	private $structure = array();
	
	public function __construct( $structure ) {
		$this->classname = "LivestatusStructure";
		$this->structure = $structure;
	}
	
	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		
		$this->write( "var livestatus_structure = ".$this->pretty_json($this->generate_array()).";" );
	}
	
	private function generate_array() {
		$result = array();
		
		foreach( $this->structure as $table => $struct ) {
			$tblresult = array();
			foreach( $struct['structure'] as $field => $type ) {
				if( is_array( $type ) ) {
					$tblresult[$field] = array( 'object', $this->lookup_class($type[0]) );
				} else {
					$tblresult[$field] = array( $type );
				}
			}
			$result[$table] = $tblresult;
		}
		
		return $result;
	}
	
	private function lookup_class( $class ) {
		foreach( $this->structure as $table => $struct ) {
			if( $struct['class'] == $class ) {
				return $table;
			}
		}
		return false;
	}
	
	private function pretty_json( $data ) {
		return str_replace( array('{','}',','), array( "{\n", "\n}",",\n" ), json_encode($data) );
	}
}
