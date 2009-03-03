<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network health widget controller
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Netw_health_Controller extends Widget_Controller {

	public function index($arguments=false)
	{
		# fetch widget view path
		$path = $this->view_path(__CLASS__, 'view');

		# assign variables to widget
		$test = 'tjobba';

		# fetch widget content
		require_once($path);

		# pass required extra resources to master template
		$this->js = array($this->widget_path.$this->widget_name(__CLASS__).'/js/netw_health');

		return $this->fetch();
	}
}