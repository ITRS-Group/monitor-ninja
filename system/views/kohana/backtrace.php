<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!is_array($trace))
	return;

echo debug::print_backtrace_as_html($trace);