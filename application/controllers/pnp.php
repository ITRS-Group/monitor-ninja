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

	public function index($host_name=false)
	{
		$host_name = urldecode($this->input->get('host_name', $host_name));

		$target_link = 'index.php';
		if (!empty($host_name)) {
				$target_link .= '?host='.$host_name;
		}
		$this->template->content = '<iframe src="/monitor/op5/pnp/'.$target_link.'" style="width: 100%; height: 600px" frameborder="0" id="iframe"></iframe>';
		$this->template->title = $this->translate->_('Reporting » PNP');
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}
}