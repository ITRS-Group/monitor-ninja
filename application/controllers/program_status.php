	<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Program status controller
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
class Program_status_Controller extends Authenticated_Controller {
	public $current = false;
	public $logos_path = '';
	public $type = 'hosts';

	public function __construct()
	{
		parent::__construct();

		$this->logos_path = Kohana::config('config.logos_path');
	}

	/**
	 * Default controller method
	 * Redirects to show_process_info() which
	 * is the equivalent of calling extinfo.cgi?type=0
	 */
	public function index()
	{

		$auth = Nagios_auth_Model::instance();
		if (!$auth->authorized_for_system_information) {
			url::redirect('extinfo/unauthorized/0');
		}

		//$items_per_page = 20;
		//$ps_model = new Program_status_Model($items_per_page, true, true);
		$ps_model = new Program_status_Model();

		$auth = Nagios_auth_Model::instance();
		if (!$auth->authorized_for_system_information) {
			url::redirect('extinfo/unauthorized/0');
		}

		$data = $ps_model->list_program_status();
		$date_format_str = nagstat::date_format();

		$i = 0;
		foreach($data as $row) {
			$result[$i][]= $row->instance_name;
			$result[$i][]= date($date_format_str, $row->last_alive);
			$result[$i][]= ($row->is_running == 1 ? _('Yes') : _('No'));
			$i++;
		}

		$this->template->title = _('Configuration').' » '._('View program status');
		$this->template->content = $this->add_view('program_status/index');
		$this->template->content->data = $result;
	}

	public function unauthorized()
	{
		$this->template->content = $this->add_view('extinfo/unauthorized');
		$this->template->disable_refresh = true;

		$this->template->content->error_description = _('If you believe this is an error, check the HTTP server authentication requirements for accessing this page and check the authorization options in your CGI configuration file.');
	}
}
