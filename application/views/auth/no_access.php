<?php defined('SYSPATH') or die('No direct access allowed.');

// Simply redirect to the login form
$url = Kohana::config('routes.log_in_form');
$url .= '?uri='.urlencode(url::current(true));
url::redirect($url);