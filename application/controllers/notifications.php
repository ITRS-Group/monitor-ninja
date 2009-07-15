<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Notifications controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Notifications_Controller extends Authenticated_Controller {
	public function index()
	{
		url::redirect('underconstruction/');
	}

	public function __call($method, $arguments)
	{
		url::redirect('underconstruction/');
	}
}
