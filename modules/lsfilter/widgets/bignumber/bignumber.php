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
	 * Filter for current objects
	 */
	private $main_filter_id = -200;

	/**
	 * Filter for which objects are "selected"
	 */
	private $selection_filter_id = -150;

	/**
	 * Some filters to make up for empty installations
	 */
	private $hardcoded_filters = array(
		-200 => array(
			'name' => 'All hosts',
			'filter' => '[hosts] all'
		),
		-150 => array(
			'name' => 'OK hosts',
			'filter' => '[hosts] state = 0 and has_been_checked = 1'
		),
		-100 => array(
			'name' => 'All services',
			'filter' => '[services] all'
		),
		-50 => array(
			'name' => 'OK services',
			'filter' => '[services] state = 0 and has_been_checked = 1'
		)
	);

	/**
	 * Display as percent
	 */
	private $display_type = false;

	/**
	 * Perform state calculation
	 */
	private $threshold_onoff = true;

	/**
	 * type of threshold
	 */
	private $threshold_type = 'lower_than';

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
	public function __construct(Widget_Model $widget_model) {
		parent::__construct($widget_model);
		$settings = $this->model->get_setting();

		if (isset($settings['main_filter_id']))
			$this->main_filter_id = $settings['main_filter_id'];

		if (isset($settings['selection_filter_id']))
			$this->selection_filter_id = $settings['selection_filter_id'];

		if (isset($settings['display_type']))
			$this->display_type = $settings['display_type'];

		if (isset($settings['threshold_type']))
			$this->threshold_type = $settings['threshold_type'];

		if (isset($settings['reverse_threshold']))
			$this->reverse_threshold = $settings['reverse_threshold'];

		if (isset($settings['threshold_onoff']))
			$this->threshold_onoff = $settings['threshold_onoff'];

		if (isset($settings['threshold_warn']))
			$this->threshold_warn = $settings['threshold_warn'];

		if (isset($settings['threshold_crit']))
			$this->threshold_crit = $settings['threshold_crit'];
	}

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Big numbers',
			'css' => array('style.css'),
			'js' => array('bignumber.js'),
		));
	}

	protected function get_suggested_title () {
		// TODO maybe change to the main filter's title? or the
		// selection, or a mix.
		$set = $this->get_set_by_filter_id($this->main_filter_id);
		return ucfirst($set->get_table());
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
	 *
	 * @return array of option and Fieldset_Model
	 */
	public function options() {
		$all_filters = array();
		foreach ($this->hardcoded_filters as $id => $filter) {
			$all_filters[$id] = $filter['name'];
		}

		$saved_filters = array();
		foreach(SavedFilterPool_Model::all()->it(false,
			array('filter_table ASC')) as $filter) {
			$all_filters[$filter->get_id()] = $filter->get_filter_name();
		}

		$show = new Fieldset_Model('SHOW');
		$show_filter = new option($this->model->get_name(), 'main_filter_id', 'Show filter', 'dropdown', array('options' => $all_filters), $this->main_filter_id);
		$show_filter->set_help('bignumber_show_filter', 'tac');
		$show[] = $show_filter;

		$with_selection = new option($this->model->get_name(), 'selection_filter_id', ' <span class="box-drawing">└</span> With selection', 'dropdown', array('options' => $all_filters), $this->selection_filter_id);
		$with_selection->set_help('bignumber_with_selection', 'tac');
		$show[] = $with_selection;

		$show[] = new option($this->model->get_name(), 'display_type', 'Unit of measurement', 'dropdown', array(
			'options' => array(
				'number_of_total' => 'Fraction',
				'number_only' => 'Count',
				'percent' => 'Percentage'
			)
		), $this->display_type);

		$options = parent::options();
		$options[] = $show;

		$show_status = new Fieldset_Model('SHOW STATUS', array('class' => 'can_be_toggled'));
		$threshold_as = new option($this->model->get_name(), 'threshold_type', 'Threshold as', 'dropdown', array(
			'options' => array(
				'lower_than' => 'Lower than',
				'higher_than' => 'Higher than',
			)
		), $this->threshold_type);
		$threshold_as->set_help('bignumber_threshold_as', 'tac');
		$show_status[] = $threshold_as;

		$show_status[] = new option($this->model->get_name(), 'threshold_onoff', 'threshold_onoff', 'input', array('type' => 'hidden'), $this->threshold_onoff);
		$show_status[] = new option($this->model->get_name(), 'threshold_warn', 'Warning threshold', 'input', array('class' => 'percentage'), $this->threshold_warn);
		$show_status[] = new option($this->model->get_name(), 'threshold_crit', 'Critical threshold', 'input', array('class' => 'percentage'), $this->threshold_crit);

		$options[] = $show_status;

		return $options;
	}

	/**
	 * @throws Exception if filter id is neither a saved filter nor
	 * hardcoded.
	 * @return ObjectSet_Model
	 */
	private function get_set_by_filter_id($filter_id) {
		if($filter_id < 0) {
			if(!array_key_exists($filter_id, $this->hardcoded_filters)) {
				throw new Exception("Bad filter given, please reconfigure this widget");
			}
			return ObjectPool_Model::get_by_query(
				$this->hardcoded_filters[$filter_id]['filter']
			);
		}

		$saved_filters = SavedFilterPool_Model::all();

		$saved_filter = $saved_filters->reduce_by('id', $filter_id, '=')->one();
		if(!$saved_filter instanceof SavedFilter_Model) {
			throw new Exception("Bad filter given, please reconfigure this widget");
		}
		return ObjectPool_Model::get_by_query(
			$saved_filter->get_filter()
		);
	}

	/**
	 * Fetch the data and show the widget
	 */
	public function index() {
		$error_msg = "";
		$main_set = $this->get_set_by_filter_id($this->main_filter_id);
		$selection_set = $this->get_set_by_filter_id($this->selection_filter_id);
		if($selection_set->get_table() !== $main_set->get_table()) {
			$error_msg = sprintf(
				"You must 'select' from the same table ('%s') your filter is for",
				$main_set->get_table()
			);
			require 'view.php';
			return;
		}

		$query = $main_set->intersect($selection_set)->get_query();

		$pool = $main_set->class_pool();
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
				$display_text = sprintf('%d / %d', $counts['selection'], $counts['all']);
				break;
		}

		$threshold_value = $counts['selection'];
		switch($this->threshold_type) {
			case 'lower_than':
				$th_func = function($val, $stat) {
					return 100.0 * $stat['selection'] / $stat['all'] < $val;
				};
				break;
			case 'higher_than':
				$th_func = function($val, $stat) {
					return 100.0 * $stat['selection'] / $stat['all'] > $val;
				};
				break;
		}

		if ($this->threshold_onoff) {
			if($th_func($this->threshold_crit, $counts)) {
				$state = 'critical';
			} else if($th_func($this->threshold_warn, $counts)) {
				$state = 'warning';
			} else {
				$state = 'ok';
			}
		} else {
			$state = 'info';
		}

		require('view.php');
	}
}
