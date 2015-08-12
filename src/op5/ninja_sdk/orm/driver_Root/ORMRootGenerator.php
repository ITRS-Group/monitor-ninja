<?php
class ORMRootGenerator extends class_generator {
	protected function generate_common() {
		$this->init_function('class_obj');
		$this->write("return %s;", false);
		$this->finish_function();

		$this->init_function('class_set');
		$this->write("return %s;", false);
		$this->finish_function();

		$this->init_function('class_pool');
		$this->write("return %s;", false);
		$this->finish_function();

		$this->init_function('key_columns');
		$this->write("return %s;", false);
		$this->finish_function();

		$this->init_function('get_table');
		$this->write("return %s;", false);
		$this->finish_function();
	}
}