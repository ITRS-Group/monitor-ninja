<?php

/**
 * A mock-implementation of the regular report options
 * In particular, it exposes a way to set arbitrary report properties and members
 */
class Test_report_options extends Report_options {
	public $properties_copy; /**< A public reference to the protected properties array */
	public $members = array(); /**< A public list of group members used by our get_report_members */
	/**
	 * Create new test report, optionally with default options
	 */
	public function __construct($options=false) {
		parent::__construct($options);
		if (isset($options->members))
			$this->members = $options->members;
		$this->properties_copy =& $this->properties;
	}

	/**
	 * Return the report members you've setup for group reports,
	 * otherwise return the report's actual members
	 */
	function get_report_members()
	{
		switch ($this['report_type']) {
		 case 'servicegroups':
		 case 'hostgroups':
			$res_members = array();
			foreach ($this->members as $group => $members) {
				if (in_array($group, $this[$this->get_value('report_type')]))
					$res_members = array_merge($res_members, $members);
			}
			return $res_members;
		}
		return parent::get_report_members();
	}
}
