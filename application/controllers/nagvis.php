<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Nagvis controller
 *
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
		$maps = new Nagvis_Maps_Model();
		$pools = new Nagvis_Rotation_Pools_Model();

		$this->template->title = _('Monitoring') . ' » NagVis';
		$this->template->breadcrumb = _('Monitoring') . ' » NagVis';
		$this->template->content = $this->add_view('nagvis/index');
		$this->template->content->maps = $mps = $maps->get_list();
		$this->template->content->pools = $pools->get_list();

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
	}

	public function view($map)
	{
		$this->template->title = _('Monitoring') . ' » NagVis » '
			. _('View') . ' » ' . $map;
		$this->template->breadcrumb = _('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. _('View') . ' » ' . $map;
		$this->template->content = $this->add_view('nagvis/view');
		$this->template->content->map = $map;


		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}

	public function edit($map)
	{
		$this->template->title = _('Monitoring') . ' » NagVis » '
		. _('Edit') . ' » ' . $map;
		$this->template->breadcrumb = _('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. _('Edit') . ' » ' . $map;
		$this->template->content = $this->add_view('nagvis/edit');
		$this->template->content->map = $map;
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}

	public function create()
	{
		$map = isset($_POST['name']) ? $_POST['name'] : 'new_map';

		$maps = new Nagvis_Maps_Model();

		if ($maps->create($map))
			url::redirect('nagvis/edit/' . $map);
		else
			url::redirect('nagvis/index');

	}

	public function delete($map)
	{
		$maps = new Nagvis_Maps_Model();

		$maps->delete($map);

		url::redirect('nagvis/index');
	}

	public function automap($object_type = '', $object_name = '')
	{
		$this->template->title = _('Monitoring') . ' » NagVis » '
			. _('Automap');
		$this->template->breadcrumb = _('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » ' .
			_('Automap');

		// Read from config and see if we have any default params set in nagvis.ini.php
		$preset = nagvisconfig::get(Kohana::config("nagvis.nagvis_real_path") . "etc/nagvis.ini.php");
		if (isset($preset['automap']['defaultparams'])) {
			$querystring = $preset['automap']['defaultparams'];
		} else {
			$querystring = '';
		}

		$supported_args = array('show', 'root', 'childLayers', 'parentLayers', 'renderMode',
					'width', 'height', 'ignoreHosts', 'filterByState', 'filterGroup',
					'search', 'rotation', 'enableHeader', 'enableHover', 'enableContext');

		foreach ($supported_args as $arg) {
			if (isset($_GET[$arg]))
				$querystring .= "&$arg=" . $_GET[$arg];
		}

		$this->template->content = $this->add_view('nagvis/automap');
		$this->template->content->mark_object_type = $object_type;
		$this->template->content->mark_object_name = $object_name;
		$this->template->content->querystring = $querystring;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}

	public function rotate($pool, $first_map)
	{
		$this->template->title = _('Monitoring') . ' » NagVis » '
			. _('Rotate') . ' » ' . $pool;
		$this->template->breadcrumb = _('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. _('Rotate') . ' » ' . $pool;
		$this->template->content = $this->add_view('nagvis/rotate');
		$this->template->content->pool = $pool;
		$this->template->content->first_map = $first_map;
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}

	public function configure()
	{
		$this->template->title = _('Monitoring') . ' » NagVis » '
			. _('Configure');
		$this->template->breadcrumb = _('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. _('Configure');
		$this->template->content = $this->add_view('nagvis/configure');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}
}

