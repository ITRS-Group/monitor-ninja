<?php

class Test_report_options extends Report_options {
	public $vtype_copy;
	public function __construct($options=false) {
		parent::__construct($options);
		$this->vtype_copy =& $this->properties;
	}
}
