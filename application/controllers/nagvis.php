<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Nagvis controller
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Nagvis_Controller extends Authenticated_Controller {

	public function index()
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$maps = new Nagvis_Maps_Model;
		$pools = new Nagvis_Rotation_Pools_Model;

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis';
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » NagVis';
		$this->template->content = $this->add_view('nagvis/index');
		$this->template->content->maps = $maps->get_list();
		$this->template->content->pools = $pools->get_list();
		$this->template->disable_refresh = true;

  		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
	}

	public function view($map)
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$maps = new Nagvis_Maps_Model;

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
			. $this->translate->_('View') . ' » ' . $map;
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('View') . ' » ' . $map;
		$this->template->content = $this->add_view('nagvis/view');
		$this->template->content->map = $map;


		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
	}

	public function edit($map)
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
		. $this->translate->_('Edit') . ' » ' . $map;
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Edit') . ' » ' . $map;
		$this->template->content = $this->add_view('nagvis/edit');
		$this->template->content->map = $map;
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
	}

	public function create()
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$map = isset($_POST['name']) ? $_POST['name'] : 'new_map';

		$maps = new Nagvis_Maps_Model;

		if ($maps->create($map))
			url::redirect('nagvis/edit/' . $map);
		else
			url::redirect('nagvis/index');
	}

	public function delete($map)
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$maps = new Nagvis_Maps_Model;

		$maps->delete($map);

		url::redirect('nagvis/index');
	}

	public function automap()
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
			. $this->translate->_('Automap');
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Automap');
		$this->template->content = $this->add_view('nagvis/automap');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
	}

	public function geomap()
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
			. $this->translate->_('Geomap');
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Geomap');
		$this->template->content = $this->add_view('nagvis/geomap');
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
	}

	public function rotate($pool, $first_map)
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
			. $this->translate->_('Rotate') . ' » ' . $pool;
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Rotate') . ' » ' . $pool;
		$this->template->content = $this->add_view('nagvis/rotate');
		$this->template->content->pool = $pool;
		$this->template->content->first_map = $first_map;
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
	}

	public function configure()
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
			. $this->translate->_('Configure');
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="/ninja/index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Configure');
		$this->template->content = $this->add_view('nagvis/configure');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
	}
}
