<?php

class LalrPreprocessorJSGenerator extends js_class_generator {
	private $grammar;

	public function __construct( $parser_name, $grammar ) {
		$this->classname = $parser_name . "Preprocessor";
		$this->grammar = $grammar->get_tokens();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);

		$this->init_class();
		foreach( $this->grammar as $name => $match ) {
			if( $name[0] != '_' ) {
				$this->generate_preprocessor( $name );
			}
		}
		$this->finish_class();
	}

	private function generate_preprocessor( $name ) {
		$this->init_function( 'preprocess_'.$name, array( 'value' ) );
		$this->write( 'return value;' );
		$this->finish_function();
	}
}