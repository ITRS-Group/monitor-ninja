<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tactical overview controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Configuration_Controller extends Authenticated_Controller {

	public $model = false;

	public function __construct()
	{
		parent::__construct();
		$this->model = new Current_status_Model();
	}

	public function configure()
	{
		$this->template->content = '<iframe src="/monitor/op5/webconfig/configure.php" style="width: 100%; height: 768px" frameborder="0"></iframe>';
		$this->template->title = $this->translate->_('Configuration » Configure');
	}
}