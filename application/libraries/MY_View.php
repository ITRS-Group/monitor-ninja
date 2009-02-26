<?php
defined('SYSPATH') or die('No direct script access.');

class View extends View_Core
{

	public function __construct($name, $data = NULL, $type = NULL)
	{
		#$name .= '.html' . $type;
		if (!Kohana::find_file('views', $name, FALSE, FALSE)) // try global extension first
		{
			if (!Kohana::find_file('views', $name, TRUE, "html")) {
				$type = false;
			} else {
				$name .= "html";
			}
		}
		parent::__construct($name, $data, $type);
	}
}
?>