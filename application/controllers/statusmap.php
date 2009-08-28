<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Statusmap controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Statusmap_Controller extends Authenticated_Controller {
	public function index()
	{
		url::redirect('underconstruction/');
	}

	public function __call($method, $arguments)
	{
		url::redirect('underconstruction/');
	}
}
