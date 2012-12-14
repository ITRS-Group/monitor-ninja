<?php

class Test_report_options extends Report_options {
	public $properties_copy;
	public $members = array();
	public function __construct($options=false) {
		parent::__construct($options);
		$this->properties_copy =& $this->properties;
	}

	function get_report_members()
	{
		switch ($this['report_type']) {
		 case 'servicegroups':
		 case 'hostgroups':
			return $this->members;
		}
		return parent::get_report_members();
	}
}
