<?php

class ORMTypeDict implements ORMTypeI {

	private $name;
	private $backend_name;
	private $options;

	public function __construct ($name, $backend_name, $options) {
		$this->name = $name;
		$this->backend_name = $backend_name;
		$this->options = $options;
	}

	public function get_default_value () {
		return "array()";
	}

	public function generate_set ($context) {
		$context->init_function( "set_{$this->name}", array('value') );

		$context->write("if(!is_array(\$value)) {");
		$context->write("throw new InvalidArgumentException('Expected associative array for set_{$this->name}');");
		$context->write("}");

		$context->write("if( \$this->{$this->name} !== \$value) {");
		$context->write("\$this->{$this->name} = \$value;");
		$context->write("\$this->_changed[%s] = true;", $this->name);
		$context->write("}");

		$context->finish_function();
	}

	public function generate_get ($context) {
		$context->init_function("get_{$this->name}");
		$context->write("return \$this->{$this->name};");
		$context->finish_function();
	}

	public function generate_save ($context) {
		$context->write("\$values['{$this->name}'] = json_encode(\$this->{$this->name});");
	}

	public function generate_iterator_set ($context) {
		$context->write("if(array_key_exists(\$prefix.'{$this->backend_name}', \$values)) {");
		$context->write("\$obj->{$this->name} = \$values[\$prefix.'{$this->backend_name}'];");
		$context->write("}");
	}

	public function generate_array_set ($context) {
		$context->write("if(array_key_exists('{$this->name}', \$values)) {" );
		$context->write("\$obj->{$this->name} = \$values['{$this->name}'];");
		$context->write("}");
	}

}
