<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Listview widget
 *
 * @author     op5 AB
 */
class Listview_Widget extends widget_Base {
	protected $duplicatable = true;

	/**
	 * Not really branding, but how to make this listview widget custom in the
	 * case of using it as a static embedded list view.
	 */
	protected $branding = array(
		'listview_link' => true
	);

	private $query = false;

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'List View'
		));
	}

	/**
	 * Disable everything configurable. This is useful when including the widget with generetated parameters from a controller.
	 */
	public function set_fixed($branding = array()) {
		$this->movable      = false;
		$this->removable    = false;
		$this->closeconfirm = false;
		$this->editable     = false;
		$this->duplicatable = false;
		$this->branding     = array_merge($this->branding, $branding);
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
