<?php defined('SYSPATH') OR die('No direct access allowed.');

class Trends_options_Core extends Report_options
{
	public function __construct($options)
	{
		parent::__construct($options);
		// Why? Because it looked like this when I found it
		$this->vtypes['initialassumedhoststate']['default'] = -3;
		$this->vtypes['initialassumedservicestate']['default'] = -3;
	}
}
