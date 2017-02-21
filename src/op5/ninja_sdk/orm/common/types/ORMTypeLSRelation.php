<?php

class ORMTypeLSRelation implements ORMTypeI {

	private $name;
	private $backend_name;
	private $options;

	public function __construct ($name, $backend_name, $options) {
		$this->name = $name;
		$this->backend_name = $backend_name;
		$this->options = $options;
	}

	public function get_default_value () {
		return "null";
	}

	public function generate_set ($context) {
		$context->init_function( "set_{$this->name}", array('value') );
		$context->write("if( \$this->{$this->name} !== \$value ) {" );
		$context->write("\$this->{$this->name} = \$value;" );
		$context->write("\$this->_changed[%s] = true;", $this->name );
		$context->write("}");
		$context->finish_function();
	}

	public function generate_get ($context) {
		$context->init_function("get_{$this->name}");
		$context->write( "return \$this->{$this->name};" );
		$context->finish_function();
	}

	public function generate_save ($context) {
		$context->write("\$values['{$this->name}'] = \$this->{$this->name};");
	}

	public function generate_iterator_set ($context) {
		$subclass = $this->options[0] . "_Model";
		/**
		 * Livestatus handles only one level of prefixes... might change in
		 * future? (for example comments: service.host.name should be host.name
		 */
		list($class, $prefix) = $this->options;
		$context->write( "\$obj->{$this->name} = {$class}_Model::factory_from_setiterator(\$values, %s, isset(\$subobj_export[%s]) ? \$subobj_export[%s] : array() );", $prefix, $this->backend_name, $this->name );
	}

	public function generate_array_set ($context) {
		$subclass = $this->options[0] . "_Model";
		$context->write("if (array_key_exists(\"{$this->name}\", \$values)) {");
		$context->write("\$obj->{$this->name} = $subclass::factory_from_array(\$values[\"{$this->name}\"], \$subobj_export[\"{$this->name}\"]);" );
		$context->write("}");
	}

}
