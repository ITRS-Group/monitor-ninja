<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tactical overview controller
 * Requires authentication
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
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
		$this->template->title = $this->translate->_('Monitoring » Tactical overview');
		$this->xtra_js[] = $this->add_path('/js/widgets.js');
		$this->template->disable_refresh = true;

		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');

		$this->template->content->links = array
		(
			$this->translate->_('logout')     => 'default/logout'
		);

		# fetch data for all widgets
		$this->model->analyze_status_data();

		$widget_info = Ninja_widget_Model::fetch_page_widgets(Router::$controller.'/'.Router::$method, $this->model);
		$widget_order = Ninja_widget_Model::fetch_widget_order(Router::$controller.'/'.Router::$method);

		if (!empty($widget_info)) {
			$settings_widgets = $widget_info['settings_widgets'];
			$settings = $widget_info['settings'];
			$widget_list = $widget_info['widget_list'];
			$this->inline_js .= $widget_info['inline_js'];
			$user_widgets = $widget_info['user_widgets'];

			# add the widgets to the page using user settings or default if not available
			foreach ($widget_list as $widget_name) {
				widget::add($widget_name, $settings[$widget_name], $this);
			}

			$this->template->settings_widgets = $settings_widgets;
			$this->template->user_widgets = $user_widgets;
			$this->template->content->widget_settings = $settings;
		}

		# Validate that we have all the widgets in our order string.
		# If this fails users will get a jGrowl error each time the page
		# reloaded.
		$tmp_arr = array();
		foreach ($widget_order as $place => $widgets) {
			$tmp_arr = array_merge($tmp_arr, (array)$widgets);
		}

		# only continue checks if sizes differs
		if (sizeof($widget_info['widget_list']) != sizeof($tmp_arr)) {
			foreach ($widget_info['widget_list'] as $tmp) {
				if (!in_array('widget-'.$tmp, $tmp_arr)) {
					$widget_order['widget-placeholder'][] = 'widget-'.$tmp;
				}
			}
		}

		# add the inline javascript to master template header
		$this->template->inline_js = $this->inline_js;

		$this->template->content->widget_order = $widget_order;
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
