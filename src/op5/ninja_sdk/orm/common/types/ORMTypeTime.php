<?php

class ORMTypeTime implements ORMTypeI {

	private $name;
	private $backend_name;
	private $options;

	public function __construct ($name, $backend_name, $options) {
		$this->name = $name;
		$this->backend_name = $backend_name;
		$this->options = $options;
	}

	public function get_default_value () {
		return "0";
	}

	public function generate_set ($context) {

		$context->init_function( "set_{$this->name}", array('value') );
		$context->conditional("\$this->{$this->name} !== \$value");


		$context->conditional('!is_int($value) && (!is_string($value) || !preg_match("/^\d+$/", $value))');
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for time '{$this->name}'\""
		);


		$context->write("}");
		$context->write( "\$this->{$this->name} = (int)\$value;" );
		$context->write( "\$this->_changed[%s] = true;", $this->name );
		$context->write( "}" );
		$context->finish_function();
	}

	public function generate_get ($context) {
		$context->init_function("get_{$this->name}");
		$context->write("return \$this->{$this->name};");
		$context->finish_function();
	}

	public function generate_save ($context) {
		$context->write("\$values['{$this->name}'] = \$this->{$this->name};");
	}

	public function generate_iterator_set ($context) {
		$context->write("if(array_key_exists(\$prefix.'{$this->backend_name}', \$values)) {");
		$context->write("\$value = \$values[\$prefix . \"{$this->backend_name}\"];");
		$context->conditional('!is_int($value) && (!is_string($value) || !preg_match("/^\d+$/", $value))');
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for time '{$this->name}'\""
		);
		$context->write("}");
		$context->write("\$obj->{$this->name} = (int)\$value;");
		$context->write("}");
	}

	public function generate_array_set ($context) {
		$context->write("if(array_key_exists('{$this->name}', \$values)) {");
		$context->write("\$value = \$values[\"{$this->name}\"];");
		$context->conditional('!is_int($value) && (!is_string($value) || !preg_match("/^\d+$/", $value))');
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for time '{$this->name}'\""
		);
		$context->write("}");
		$context->write("\$obj->{$this->name} = (int)\$value;");
		$context->write("}");
	}

}
