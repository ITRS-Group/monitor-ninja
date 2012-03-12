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

		# make sure we have this done before letting widgets near
		$model = Current_status_Model::instance();
		$model->analyze_status_data();

		# fetch data for all widgets
		$widget_objs = Ninja_widget_Model::fetch_all(Router::$controller.'/'.Router::$method);
		$widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);

		if (empty($widgets)) {
			# probably a new user, we should populate the widget list
			# yeah, this does Weird Things™ if a user should try to hide everything
			# but that is a silly thing to do, so just blame the user.
			foreach ($widget_objs as $obj) {
				$obj->save();
			}
			$widgets = widget::add_widgets(Router::$controller.'/'.Router::$method, $widget_objs, $this);
		}

		if (array_keys($widgets) == array('unknown')) {
			$nwidgets = count($widgets['unknown']);
			$widgets['widget-placeholder'] = array_splice($widgets['unknown'], 0, round($nwidgets/4));
			$widgets['widget-placeholder1'] = array_splice($widgets['unknown'], 0, round($nwidgets/4));
			$widgets['widget-placeholder2'] = array_splice($widgets['unknown'], 0, round($nwidgets/4));
			$widgets['widget-placeholder3'] = $widgets['unknown'];
			unset($widgets['unknown']);
		} else if (isset($widgets['unknown'])) {
			$widgets['widget-placeholder'] = array_merge($widgets['widget-placeholder'], $widgets['unknown']);
			unset($widgets['unknown']);
		}

		$this->template->content->widgets = $widgets;
		$this->template->widgets = $widget_objs;
		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
		$this->template->inline_js = $this->inline_js;
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
