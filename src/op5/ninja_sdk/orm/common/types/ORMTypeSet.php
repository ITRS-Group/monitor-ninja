<?php

class ORMTypeSet implements ORMTypeI {

	private $name;
	private $backend_name;
	private $options;
	private $set_class;

	public function __construct ($name, $backend_name, $options) {

		$this->name = $name;
		$this->backend_name = $backend_name;
		$this->options = $options;

		if (!isset($options[1])) {
			throw new ORMGeneratorException("Missing option (ORM object type) for ORMTypeSet");
		}

		$this->set_class = $options[1] . "Set_Model";
		$this->pool_class = $options[1] . "Pool_Model";

	}

	public function get_default_value () {
		return "null";
	}

	public function generate_set ($context) {
		$context->init_function( "set_{$this->name}", array('value') );

		$context->write("try {");
		$context->write("if (is_object(\$value) && is_a(\$value, '{$this->set_class}')) {");
		$context->write("\$set = \$value;");
		$context->write("} elseif (is_string(\$value)) {");
		$context->write("\$set = ObjectPool_Model::get_by_query(\$value);");
		$context->write("} else {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for set '{$this->name}'\""
		);
		$context->write("}");
		$context->write("\$this->{$this->name} = \$set;");
		$context->write("\$this->_changed[%s] = true;", $this->name);
		$context->write("} catch (LSFilterException \$e) {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for set '{$this->name}'\""
		);
		$context->write("}");

		$context->finish_function();
	}

	public function generate_get ($context) {
		$context->init_function("get_{$this->name}");
		$context->write("if (\$this->{$this->name}) {");
		$context->write("return \$this->{$this->name};");
		$context->write("} else {");
		$context->write("return {$this->pool_class}::none();");
		$context->write("}");
		$context->finish_function();
	}

	public function generate_save ($context) {
		$context->write("\$values['{$this->name}'] = \$this->{$this->name};");
	}

	public function generate_iterator_set ($context) {
		$context->write("if(array_key_exists(\$prefix.'{$this->backend_name}', \$values)) {");
		$context->write("\$value = \$values[\$prefix.'{$this->backend_name}'];");
		$context->write("try {");
		$context->write("if (is_object(\$value) && is_a(\$value, '{$this->set_class}')) {");
		$context->write("\$set = \$value;");
		$context->write("} elseif (is_string(\$value)) {");
		$context->write("\$set = ObjectPool_Model::get_by_query(\$value);");
		$context->write("} else {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for set '{$this->name}'\""
		);
		$context->write("}");
		$context->write("\$obj->{$this->name} = \$set;");
		$context->write("} catch (LSFilterException \$e) {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for set '{$this->name}'\""
		);
		$context->write("}");
		$context->write("}");
	}

	public function generate_array_set ($context) {
		$context->write("if(array_key_exists('{$this->name}', \$values)) {" );
		$context->write("\$value = \$values['{$this->name}'];");
		$context->write("try {");
		$context->write("if (is_object(\$value) && is_a(\$value, '{$this->set_class}')) {");
		$context->write("\$set = \$value;");
		$context->write("} elseif (is_string(\$value)) {");
		$context->write("\$set = ObjectPool_Model::get_by_query(\$value);");
		$context->write("} else {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for set '{$this->name}'\""
		);
		$context->write("}");
		$context->write("\$obj->{$this->name} = \$set;");
		$context->write("} catch (LSFilterException \$e) {");
		$context->raise(
			'InvalidArgumentException',
			"\"'\" . gettype(\$value) . \"' is not valid for set '{$this->name}'\""
		);
		$context->write("}");
		$context->write("}");
	}

}
