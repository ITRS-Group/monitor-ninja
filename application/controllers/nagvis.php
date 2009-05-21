<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Nagvis controller
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class Nagvis_Controller extends Authenticated_Controller {

	public function index()
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$maps = new Nagvis_Maps_Model;

		$this->template->content = $this->add_view('nagvis/index');

		$this->template->content->maps = $maps->get_list();

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css = array_merge($this->xtra_css, array($this->add_path('/css/common.css')));
	}
}
