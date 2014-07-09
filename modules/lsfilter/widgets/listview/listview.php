<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Listview widget
 *
 * @author     op5 AB
 */
class Listview_Widget extends widget_Base {
	protected $duplicatable = true;

	private $query=false;

	public function __construct($widget_model) {
		parent::__construct($widget_model);
	}

	/**
	 * Disable everything configurable. This is useful when including the widget with generetated parameters from a controller.
	 */
	public function set_fixed() {
		$this->movable      = false;
		$this->removable    = false;
		$this->closeconfirm = false;
		$this->editable     = false;
		$this->duplicatable = false;
	}

	public function options() {
		$options = array();
		$options[] = new option('listview','query',_('Query'),'textarea',array(),'[hosts] all');
		$options[] = new option('listview','columns',_('Columns'),'textarea',array(),'all');
		$options[] = new option('listview','limit',_('Limit'),'input',array(),20);
		$options[] = new option('listview','order',_('Default order column'),'input',array(),'');
		return $options;
	}

	public function index() {

		$this->args = $this->get_arguments();
		require($this->view_path('view'));
	}
}
