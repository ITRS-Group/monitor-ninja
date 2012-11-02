<?php

abstract class LivestatusClassGenerator {
	protected $fp;
	protected $indent_lvl = 0;
	protected $classname;
	
	public function generate( $fp ) {
		$this->fp = $fp;
		/* Hardcode, so we don't accidentaly add whitespace before < */
		fwrite( $this->fp, "<?php\n\n" );
	}
	
	public function get_classname() {
		return $this->classname;
	}
	
	protected function classfile( $path ) {
		$this->write( 'requrire_once( '. var_export($path, true) . ' );' );
	}
	
	protected function init_class( $parent = false ) {
		$this->write();
		$this->write( "class ".$this->classname.($parent===false?"":" extends $parent")." {" );
	}
	
	protected function finish_class() {
		$this->write( "}" );
	}
	
	protected function variable( $name, $default = null, $visibility = 'private' ) {
		$this->write( "$visibility \$$name = " . var_export( $default, true ) . ";" );
	}
	
	protected function init_function( $name, $args = array() ) {
		$argstr = implode(', ', array_map(function($n){return '$'.$n;},$args));
		$this->write();
		$this->write( "public function $name($argstr) {" );
	}
	
	protected function finish_function() {
		$this->write( "}" );
	}
	
	protected function write( $block = '' ) {
		$lines = explode( "\n", $block );
		foreach( $lines as $line ) {
			$this->indent_lvl -= substr_count( $line, '}' );
			fwrite( $this->fp, str_repeat( "\t", $this->indent_lvl ) . $line . "\n" );
			$this->indent_lvl += substr_count( $line, '{' );
		}
	}
}