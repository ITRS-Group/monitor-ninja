<?php

class Test_report_options extends Report_options {
	public $properties_copy;
	public function __construct($options=false) {
		parent::__construct($options);
		$this->properties_copy =& $this->properties;
	}
}
