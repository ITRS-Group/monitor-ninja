<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tactical overview controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Pnp_Controller extends Authenticated_Controller {

	public $model = false;

	public function __construct()
	{
		parent::__construct();
		$this->model = new Current_status_Model();
	}

	public function index($host=false, $srv=false)
	{
		$host = urldecode($this->input->get('host_name', $host));
		$srv = urldecode($this->input->get('service_name', $srv));

		$target_link = 'index.php';
		if (!empty($host))
				$target_link .= '?host='.$host;
		if (!empty($srv))
			$target_link .= '&srv='.$srv;

		$this->template->content = '<iframe src="/monitor/op5/pnp/'.$target_link.'" style="width: 100%; height: 600px" frameborder="0" id="iframe"></iframe>';
		$this->template->title = $this->translate->_('Reporting » PNP');
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}
}