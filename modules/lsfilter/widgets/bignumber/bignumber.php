<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * bignumber widget
 */
class bignumber_Widget extends widget_Base {
	/**
	 * Set if this widget is duplicatable
	 */
	protected $duplicatable = true;

	/**
	 * Text below the value
	 */
	private $legend = 'Hosts';

	/**
	 * Filter for current objects
	 */
	private $main_filter = '[hosts] all';

	/**
	 * Filter for which objects are "selected"
	 */
	private $selection_filter = '[hosts] state = 0';

	/**
	 * Display as percent
	 */
	private $display_type = false;

	/**
	 * type of threshold
	 */
	private $threshold_type = false;

	/**
	 * Threshold for warning (orange)
	 */
	private $threshold_warn = 0.0;

	/**
	 * Threshold for critical (red)
	 */
	private $threshold_crit = 0.0;

	/**
	 * Constructor. This should be overloaded, to upadte the settings-attribute
	 * when making a custom widget of this type
	 */
	public function __construct(Ninja_Widget_Model $widget_model) {
		parent::__construct($widget_model);
		
		if(isset($this->model->setting['legend']))
			$this->legend = $this->model->setting['legend'];
		
		if(isset($this->model->setting['main_filter']))
			$this->main_filter = $this->model->setting['main_filter'];
		
		if(isset($this->model->setting['selection_filter']))
			$this->selection_filter = $this->model->setting['selection_filter'];
		
		if(isset($this->model->setting['display_type']))
			$this->display_type = $this->model->setting['display_type'];
		
		if(isset($this->model->setting['threshold_type']))
			$this->threshold_type = $this->model->setting['threshold_type'];

		if(isset($this->model->setting['reverse_threshold']))
			$this->reverse_threshold = $this->model->setting['reverse_threshold'];
		
		if(isset($this->model->setting['threshold_warn']))
			$this->threshold_warn = $this->model->setting['threshold_warn'];
		
		if(isset($this->model->setting['threshold_crit']))
			$this->threshold_crit = $this->model->setting['threshold_crit'];
	}

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Big numbers',
			'css' => array('style.css')
		));
	}

	/**
	 * Disable everything configurable. This is useful when including the widget with generated parameters from a controller.
	 */
	public function set_fixed() {
		$this->movable      = false;
		$this->removable    = false;
		$this->closeconfirm = false;
		$this->editable     = false;
		$this->duplicatable = false;
	}

	/**
	 * Load the options for this widget.
	 */
	public function options() {
		$options = parent::options();
		$options[] = new option($this->model->name, 'legend', 'Legend', 'input', array(), $this->legend);
		$options[] = new option($this->model->name, 'main_filter', 'Filter', 'textarea', array(), $this->main_filter);
		$options[] = new option($this->model->name, 'selection_filter', 'Selection Filter', 'textarea', array(), $this->selection_filter);
		$options[] = new option($this->model->name, 'display_type', 'Display as', 'dropdown', array(
			'options' => array(
				'number_of_total' => 'Number vs. total',
				'number_only' => 'Only number',
				'percent' => 'Percentage'
			)
		), $this->display_type);
		$options[] = new option($this->model->name, 'threshold_type', 'Threshold as', 'dropdown', array(
			'options' => array(
				'lt_pct' => 'less than (percentage)',
				'gt_pct' => 'greater than (percentage)',
				'lt_match' => 'less than (objects matching)',
				'gt_match' => 'greater than (objects matching)',
				'lt_left' => 'less than (objects not matching)',
				'gt_left' => 'greater than (objects not matching)'
			)
		), $this->threshold_type);
		$options[] = new option($this->model->name, 'threshold_warn', 'Warning threshold value', 'input', array(), $this->threshold_warn);
		$options[] = new option($this->model->name, 'threshold_crit', 'Critical threshold value', 'input', array(), $this->threshold_crit);
		return $options;
	}

	/**
	 * Fetch the data and show the widget
	 */
	public function index() {
		try {
			$main_set = ObjectPool_Model::get_by_query($this->main_filter);
			$selection_set = ObjectPool_Model::get_by_query($this->selection_filter);

			$pool = $main_set::class_pool();
			$all_set = $pool::all();

			$counts = $main_set->stats(array(
				'all' => $all_set,
				'selection' => $selection_set
			));

			switch($this->display_type) {
				case 'percent':
					$display_text = sprintf("%0.1f%%", 100.0 * $counts['selection'] / $counts['all']);
					break;
				case 'number_only':
					$display_text = sprintf("%d", $counts['selection']);
					break;
				case 'number_of_total':
				default:
					$display_text = sprintf("%d / %d", $counts['selection'], $counts['all']);
					break;
			}

			$threshold_value = $counts['selection'];
			switch($this->threshold_type) {
				case 'lt_pct':
					$th_func = function($val, $stat) {
						return 100.0 * $stat['selection'] / $stat['all'] < $val;
					};
					break;
				case 'gt_pct':
					$th_func = function($val, $stat) {
						return 100.0 * $stat['selection'] / $stat['all'] > $val;
					};
					break;
				case 'lt_match':
					$th_func = function($val, $stat) {
						return $stat['selection'] < $val;
					};
					break;
				case 'gt_match':
					$th_func = function($val, $stat) {
						return $stat['selection'] > $val;
					};
					break;
				case 'lt_left':
					$th_func = function($val, $stat) {
						return $stat['all'] - $stat['selection'] < $val;
					};
					break;
				case 'gt_left':
					$th_func = function($val, $stat) {
						return $stat['all'] - $stat['selection'] > $val;
					};
					break;
				default:
					$th_func = function($val, $stat) {
						return false; // Default to never a problem
					};
			}

			if($th_func($this->threshold_crit, $counts)) {
				$state = 'critical';
			} else if($th_func($this->threshold_warn, $counts)) {
				$state = 'warning';
			} else {
				$state = 'ok';
			}

			require('view.php');
		} catch( ORMException $e ) {
			require('view_error.php');
		} catch( op5LivestatusException $e ) {
			require('view_error.php');
		} catch( Exception $e ) {
			require('view_error.php');
		}
	}
}
