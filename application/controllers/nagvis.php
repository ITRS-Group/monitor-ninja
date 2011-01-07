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
		$_SESSION['nagvis_user'] = user::session('username');

		$maps = new Nagvis_Maps_Model;
		$pools = new Nagvis_Rotation_Pools_Model;

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis';
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » NagVis';
		if (!$this->can_use_nagvis())
			return;

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
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('View') . ' » ' . $map;

		if (!$this->can_use_nagvis())
			return;

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
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
		. $this->translate->_('Edit') . ' » ' . $map;
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Edit') . ' » ' . $map;

		if (!$this->can_use_nagvis())
			return;

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
		if (!$this->can_use_nagvis())
			return;

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
		if (!$this->can_use_nagvis())
			return;

		$_SESSION['nagvis_user'] = user::session('username');

		$maps = new Nagvis_Maps_Model;

		$maps->delete($map);

		url::redirect('nagvis/index');
	}

	public function automap($object_type = '', $object_name = '')
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
			. $this->translate->_('Automap');
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » ' .
			$this->translate->_('Automap');

		if (!$this->can_use_nagvis())
			return;

		$querystring = '';
		if (isset($_GET['renderMode']))
			$querystring .= '&renderMode=' . $_GET['renderMode'];
		if (isset($_GET['root']))
			$querystring .= '&root=' . $_GET['root'];
		if (isset($_GET['maxLayers']))
			$querystring .= '&maxLayers=' . $_GET['maxLayers'];
		if (isset($_GET['width']))
			$querystring .= '&width=' . $_GET['width'];
		if (isset($_GET['height']))
			$querystring .= '&height=' . $_GET['height'];
		if (isset($_GET['ignoreHosts']))
			$querystring .= '&ignoreHosts=' . $_GET['ignoreHosts'];
		if (isset($_GET['filterGroup']))
			$querystring .= '&filterGroup=' . $_GET['filterGroup'];

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

	public function geomap($object_type = '', $object_name = '')
	{
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
			. $this->translate->_('Geomap');
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Geomap');

		if (!$this->can_use_nagvis())
			return false;

		$this->template->content = $this->add_view('nagvis/geomap');
		$this->template->content->mark_object_type = $object_type;
		$this->template->content->mark_object_name = $object_name;
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
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Rotate') . ' » ' . $pool;

		if (!$this->can_use_nagvis())
			return;

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
		$_SESSION['nagvis_user'] = user::session('username');

		$this->template->title = $this->translate->_('Monitoring') . ' » NagVis » '
			. $this->translate->_('Configure');
		$this->template->breadcrumb = $this->translate->_('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » '
			. $this->translate->_('Configure');

		if (!$this->can_use_nagvis())
			return;

		$this->template->content = $this->add_view('nagvis/configure');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->xtra_css = array($this->add_path('/css/default/nagvis.css'));
		$this->template->css_header->css = $this->xtra_css;
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}

	/**
	 * When a user isn't a contact, (s)he isn't allowed to use anything in
	 * nagvis. As ninja extracts data from nagvis behind its back, this helps
	 * us be consistent.
	 */
	private function can_use_nagvis()
	{
		$res = true;
		$auth = new Nagios_auth_Model();
		if (!$auth->id)
			$res = false;

		if (!$res) {
			$this->template->content = $this->add_view('unauthorized');
			$this->template->content->error_description = $this->translate->_('If you believe this is an error, check the HTTP server authentication requirements for accessing this page and check the authorization options in your CGI configuration file.');
			$this->template->content->error_message = $this->translate->_('It appears as though you do not have permission to access nagvis');
		}

		return $res;
	}
}
