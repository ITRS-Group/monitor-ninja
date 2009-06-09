<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tactical overview controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Tac_Controller extends Authenticated_Controller {

	public $model = false;

	public function __construct()
	{
		parent::__construct();
		$this->model = new Current_status_Model();
	}

	public function index()
	{
		$this->template->content = $this->add_view('tac/index');
		$this->template->title = $this->translate->_('TAC::index');

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->template->content->links = array
		(
			$this->translate->_('logout')     => 'default/logout'
		);

		# data for widgets
		$this->model->analyze_status_data();
		$all_widgets = Ninja_widget_Model::fetch_widgets(Router::$controller.'/'.Router::$method, true);
		$settings_widgets = false;
		if (!empty($all_widgets)) {
			foreach ($all_widgets as $row) {
				$settings = unserialize($row->setting);
				$settings[] = $this->model; # add this model for each widget
				widget::add($row->name, $settings, $this);
				$settings_widgets['widget-'.$row->name] = $row->friendly_name;
			}
		}
		$this->template->settings_widgets = $settings_widgets;
		$widgets = Ninja_widget_Model::fetch_widgets(Router::$controller.'/'.Router::$method);

		$user_widgets = false;
		if (!empty($widgets)) {
			foreach ($widgets as $w) {
				$user_widgets['widget-'.$w->name] = $w->friendly_name;
			}
		}
		if (!empty($user_widgets)) {
			# customized settings detected
			# some widgets should possibly be hidden
			foreach ($settings_widgets as $id => $w) {
				if (!array_key_exists($id, $user_widgets)) {
					$this->inline_js .= "\$.fn.HideEasyWidget('".$id."');\n";
				}
			}
		}
		$this->template->inline_js = $this->inline_js;
		$this->template->user_widgets = $user_widgets;

		$this->xtra_js[] = $this->add_path('/js/tac_widgets.js');
		$this->template->content->widgets = $this->widgets;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
	}

	/**
	 * AJAX test from a POST form
	 * Return value is echo:ed back as JSON data using the ZEND_Json class
	 * This is also an example using the zend::instance() helper method
	 * to instantiate the ZEND class.
	 *
	 * Since this is a method of a class extending the Authenticated_Controller,
	 * any request from a user that isn't logged in will redirect to login form.
	 */
	public function ajax_test()
	{
		if(request::is_ajax()) {
			$this->auto_render=false;
			$user_id = $this->input->post('user_id', 0);
			$json = zend::instance('JsOn');
			$var = array('username' => user::session('username').': '.user::session('access'), 'id' => $user_id);
			$json_str = $json->encode($var);
			echo $json_str;
		} else {
			echo "Can't seem to identify request as AJAX<br />";
			echo request::method();
		}
	}

	public function ajax_host_lookup()
	{
		if(request::is_ajax()) {
			# the profiler seems to interfere with ajax calls
			# so we disable it here if enabled
			if ($this->profiler)
				$this->profiler->disable();
			$this->auto_render=false;
			$host_info = $this->input->post('host_info', false);
			if (!empty($host_info)) {
				exec('host '.$host_info, $output, $retval);
				if ($retval==0 && !empty($output)) {
					$host_info = false;
					foreach ($output as $line) {
						if (strstr($line, 'has address')) {
							$parts = false;
							$parts = explode(' ', $line);
							if (!empty($parts)) {
								$host_info[] = $parts[sizeof($parts)-1];
							} else {
								$host_info[] = $line;
							}
						} elseif (strstr($line, 'domain name pointer')) {
							$parts = false;
							$parts = explode(' ', $line);
							if (!empty($parts)) {
								$host_info = $parts[sizeof($parts)-1];
								$host_info = substr_replace($host_info, '', -1, 1);
							} else {
								$host_info = $line;
							}
						} else {
							// do nuthin, we're not interested in other stuff
							//$host_info .= $line."<br />";
						}
					}
				} else {
					$host_info = false;
				}
				$host_info = !empty($host_info) ? $host_info : '--no response--';
				if (is_array($host_info)) {
					sort($host_info);
					$host_info = implode('<br />', $host_info);
				}
				$json = zend::instance('JsOn');
				$var = array('response' => $host_info);
				$json_str = $json->encode($var);
				echo $json_str;
			}
		} else {
			echo "Can't seem to identify request as AJAX<br />";
			echo request::method();
		}
	}
}