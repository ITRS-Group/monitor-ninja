<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Listview widget
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Listview_Widget extends widget_Base {
	protected $duplicatable = true;
	
	private $query=false;
	
	public function __construct($widget_model) {
		parent::__construct($widget_model);
		$basepath = 'modules/lsfilter/';
		$ormpath = 'modules/orm/';
		
		$this->js[] = $ormpath.'js/LivestatusStructure';
		
		$this->js[] = $basepath.'js/LSFilter';
		$this->js[] = $basepath.'js/LSFilterLexer';
		$this->js[] = $basepath.'js/LSFilterParser';
		$this->js[] = $basepath.'js/LSFilterPreprocessor';
		$this->js[] = $basepath.'js/LSFilterVisitor';
		
		$this->js[] = $basepath.'views/themes/default/js/lib';
		$this->js[] = $basepath.'views/themes/default/js/LSFilterVisitors';
		$this->js[] = $basepath.'views/themes/default/js/LSFilterRenderer';
		
		$this->js[] = $basepath.'views/themes/default/js/LSFilterList';
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
		$options = parent::options();
		$options[] = new option('listview','query',_('Query'),'textarea',array(),'[hosts] all');
		$options[] = new option('listview','limit',_('Limit'),'input',array(),20);
		return $options;
	}
	
	public function index() {
		
		$this->args = $this->get_arguments();
		try {
			$set = ObjectPool_Model::get_by_query($this->args['query']);
		} catch(LSFilterException $e) {
			array(
				'status' => 'error',
				'data' => $e->getMessage().' at "'.substr($e->get_query(), $e->get_position()).'"',
				'query' => $e->get_query(),
				'position' => $e->get_position()
				);
		}
		require($this->view_path('view'));
	}
}