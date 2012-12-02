<?php

class LalrVisitorPHPGenerator extends class_generator {
	private $grammar;
	
	public function __construct( $parser_name, $grammar ) {
		$this->classname = $parser_name . "Visitor";
		$this->grammar = $grammar->get_rules();
		$this->set_library();
	}
	
	public function generate() {
		parent::generate();
		
		$this->init_class( false, array('abstract') );
		foreach( $this->grammar as $name => $item ) {
			if( $name[0] != '_' ) {
				$this->generate_visitor( $name, $item );
			}
		}
		$this->generate_acceptor();
		$this->finish_class();
	}
	
	private function generate_visitor( $name, $item ) {
		$item_name = $item->get_name();
		
		/* Transparent rules doesn't have a visitor method */
		if( $item_name[0] == '_' )
			return;
		
		$args = array();
		foreach( $item->get_symbols() as $i => $symbol ) {
			if( $item->symbol_enabled($i) ) {
				$args[] = $symbol.$i;
			}
		}
		
		$this->comment( strval( $item ) );
		$this->abstract_function( 'visit_'.$name, $args );
	}
	
	private function generate_acceptor() {
		$this->abstract_function( 'accept', array('result') );
	}
}