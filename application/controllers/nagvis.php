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
		$pools = new Nagvis_Rotation_Pools_Model;

		$this->template->title = 'NagVis';
		$this->template->breadcrumb = 'NagVis';
		$this->template->content = $this->add_view('nagvis/index');
		$this->template->content->maps = $maps->get_list();
		$this->template->content->pools = $pools->get_list();
		$this->template->disable_refresh = true;

  		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
	}

	public function view($map)
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$maps = new Nagvis_Maps_Model;

		$this->template->title = 'NagVis » ' . $this->translate->_('View') . ' » ' . $map;
		$this->template->breadcrumb = '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('View') . ' » ' . $map;
		$this->template->content = $this->add_view('nagvis/view');
		$this->template->content->map = $map;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
	}

	public function edit($map)
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = 'NagVis » ' . $this->translate->_('Edit') . ' » ' . $map;
		$this->template->breadcrumb = '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Edit') . ' » ' . $map;
		$this->template->content = $this->add_view('nagvis/edit');
		$this->template->content->map = $map;
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
	}

	public function automap()
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = 'NagVis » ' . $this->translate->_('Automap');
		$this->template->breadcrumb = '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Automap');
		$this->template->content = $this->add_view('nagvis/automap');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
	}

	public function geomap()
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = 'NagVis » ' . $this->translate->_('Geomap');
		$this->template->breadcrumb = '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Geomap');
		$this->template->content = $this->add_view('nagvis/geomap');
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
	}

	public function rotate($pool, $first_map)
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = 'NagVis » ' . $this->translate->_('Rotate') . ' » ' . $pool;
		$this->template->breadcrumb = '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Rotate') . ' » ' . $pool;
		$this->template->content = $this->add_view('nagvis/rotate');
		$this->template->content->pool = $pool;
		$this->template->content->first_map = $first_map;
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
	}
}
