<?php

class ORMTypeRelation implements ORMTypeI {

	private $name;
	private $backend_name;
	private $options;
	private $object_class;
	private $set_class;
	private $pool_class;

	public function __construct ($name, $backend_name, $options) {

		$this->name = $name;
		$this->backend_name = $backend_name;
		$this->options = $options;

		if (!isset($options[1])) {
			throw new ORMGeneratorException("Missing option (ORM object type) for ORMTypeSet");
		}

		$this->object_class = $options[1] . "_Model";
		$this->set_class = $options[1] . "Set_Model";
		$this->pool_class = $options[1] . "Pool_Model";

	}

	public function get_default_value () {
		return "null";
	}

	public function generate_set ($context) {
		$context->init_function( "set_{$this->name}", array('value') );

		$context->conditional(
			"is_a(\$value, \"{$this->object_class}\")",
			"is_string(\$value)"
		);
		$context->write("if (is_string(\$value)) {");
		$context->write("\$value = {$this->pool_class}::fetch_by_key(\$value);");
		$context->write("}");
		$context->write("if(!\$this->{$this->name} || (\$this->{$this->name}->get_key() !== \$value->get_key())) {");
		$context->write("\$this->{$this->name} = \$value;");
		$context->write("\$this->_changed[%s] = true;", $this->name);
		$context->write("}");
		$context->write("} else {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for relation '{$this->name}'\""
		);
		$context->write("}");


		$context->finish_function();
	}

	public function generate_get ($context) {
		$context->init_function("get_{$this->name}");

		$context->write("if (is_null(\$this->{$this->name})) {");
		$context->write("return null;");
		$context->write("} else {");
		$context->write("return \$this->{$this->name};");
		$context->write("}");

		$context->finish_function();
	}

	public function generate_save ($context) {
		$context->write("\$values['{$this->name}'] = \$this->{$this->name}->get_key();");
	}

	public function generate_iterator_set ($context) {
		$context->write("if(array_key_exists(\$prefix.'{$this->backend_name}', \$values)) {");
		$context->write("\$value = \$values[\$prefix.'{$this->backend_name}'];");
		$context->conditional(
			"is_a(\$value, \"{$this->object_class}\")",
			"is_string(\$value)"
		);
		$context->write("if (is_string(\$value)) {");
		$context->write("\$value = {$this->pool_class}::fetch_by_key(\$value);");
		$context->write("}");
		$context->write("\$obj->{$this->name} = \$value;");
		$context->write("} else {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for relation '{$this->name}'\""
		);
		$context->write("}");
		$context->write("}");
	}

	public function generate_array_set ($context) {
		$context->write("if(array_key_exists('{$this->name}', \$values)) {" );
		$context->write("\$value = \$values['{$this->name}'];");
		$context->conditional(
			"is_a(\$value, \"{$this->object_class}\")",
			"is_string(\$value)"
		);
		$context->write("if (is_string(\$value)) {");
		$context->write("\$value = {$this->pool_class}::fetch_by_key(\$value);");
		$context->write("}");
		$context->write("\$obj->{$this->name} = \$value;");
		$context->write("} else {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for relation '{$this->name}'\""
		);
		$context->write("}");
		$context->write("}");
	}

}
