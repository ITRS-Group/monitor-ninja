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
class Pnp_Controller extends Authenticated_Controller {
	/**
	 * Main page for PNP
	 * @param $host string
	 * @param $srv string
	 */
	public function index($host=false, $srv=false)
	{
		$host = $this->input->get('host', $host);
		$srv = $this->input->get('srv', $srv);

		if(!$host) {
			$host = '.pnp-internal';
		}

		$target_link = pnp::url($host, $srv);
		$this->template->content = '<iframe src="'.$target_link.'" style="width: 100%; height: 600px" frameborder="0" id="iframe"></iframe>';
		$this->template->title = _('Reporting » Graphs');
	}

	/**
	 * Save prefered graph for a specific param
	 */
	public function pnp_default()
	{

		/* Ajax calls shouldn't be rendered. This doesn't, because some unknown
		 * magic doesn't render templates in ajax requests, but for debugging
		 */
		$this->auto_render = false;

		$param = $this->input->post('param', false);
		$param = pnp::clean($param);
		$pnp_path = Kohana::config('config.pnp4nagios_path');

		if ($pnp_path != '') {
			$source = intval($this->input->post('source', false));
			$view = intval($this->input->post('view', false));
			$settings = json_encode(array("source"=>$source, "view"=>$view));

			Ninja_setting_Model::save_page_setting('default_graph', $pnp_path.'/image?'.$param, $settings);
		}
	}

	/**
	 * Get graph setting for a specific host/service
	 */
	public function get_pnp_default_graph_setting()
	{

		/* Ajax calls shouldn't be rendered. This doesn't, because some unknown
		 * magic doesn't render templates in ajax requests, but for debugging
		 */
		$this->auto_render = false;

		$page = $this->input->post('page', false);

		$pnp_path = Kohana::config('config.pnp4nagios_path');

		if ($pnp_path != '') {
			$settings = Ninja_setting_Model::fetch_page_setting('default_graph', $page);
			
			// Returns default settings if no setting present
			if ($settings){
				echo $settings->setting;
			} else {
				http_response_code(404);
				echo "Default graph setting not found";
			}
		}
	}

}
