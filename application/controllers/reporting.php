<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tactical overview controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Reporting_Controller extends Authenticated_Controller {

	public $model = false;

	public function __construct()
	{
		parent::__construct();
		$this->model = new Current_status_Model();
	}

	public function availability()
	{
		$this->template->content = '<iframe src="/monitor/op5/reports/gui/avail_setup.php" style="width: 100%; height: 600px" frameborder="0" id="iframe"></iframe>';
		$this->template->title = $this->translate->_('Reporting » Availability');
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}

	public function sla_reporting()
	{
		$this->template->content = '<iframe src="/monitor/op5/reports/gui/sla/" style="width: 100%; height: 600px" frameborder="0" id="iframe"></iframe>';
		$this->template->title = $this->translate->_('Reporting » SLA Reporting');
		$this->template->js_header = $this->add_view('js_header');
		$this->xtra_js = array($this->add_path('/js/iframe-adjust.js'));
		$this->template->js_header->js = $this->xtra_js;
	}
}