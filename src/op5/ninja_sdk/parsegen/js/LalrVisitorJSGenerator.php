<?php

class LalrVisitorJSGenerator extends js_class_generator {
	private $grammar;

	public function __construct( $parser_name, $grammar ) {
		$this->classname = $parser_name . "Visitor";
		$this->grammar = $grammar->get_rules();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);

		$this->init_class();
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
				$args[] = rtrim($symbol,'0123456789').$i;
			}
		}

		$this->comment( strval( $item ) );
		$this->init_function( 'visit_'.$name, $args );
		$this->finish_function();
	}

	private function generate_acceptor() {
		$this->init_function( 'accept', array('result') );
		$this->finish_function();
	}
}