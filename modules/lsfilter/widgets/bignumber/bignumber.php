<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * bignumber widget
 */
class bignumber_Widget extends widget_Base {
	protected $duplicatable = true;

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
		-151 => array(
			'name' => 'Unhandled host problems',
			'filter' => '[hosts] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0'
		),
		-100 => array(
			'name' => 'All services',
			'filter' => '[services] all'
		),
		-50 => array(
			'name' => 'OK services',
			'filter' => '[services] state = 0 and has_been_checked = 1'
		),
		-51 => array(
			'name' => 'Unhandled service problems',
			'filter' => '[services] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0 and host.scheduled_downtime_depth = 0'
		),
	);

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 *
	 * @return array
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'Big numbers',
			'css' => array('style.css')
		));
	}

	protected function get_suggested_title () {
		$form_model = $this->options();
		$hardcoded_filters = $this->hardcoded_filters;
		$saved_filter_name = function($filter_id) use ($hardcoded_filters) {
			if($filter_id < 0) {
				return $hardcoded_filters[$filter_id]['name'];
			}
			$filter = SavedFilterPool_Model::all()->reduce_by('id', $filter_id, '=')->one();
			return $filter->get_filter_name();
		};
		return sprintf("%s: %s",
			$saved_filter_name($form_model->get_value('main_filter_id', -200)),
			$saved_filter_name($form_model->get_value('selection_filter_id', -150))
		);
	}

	/**
	 * Disable everything configurable. This is useful when including the
	 * widget with generated parameters from a controller.
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
	 * @return Form_Model
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

		$main_filter = new Form_Field_Option_Model('main_filter_id', 'Show filter', $all_filters);
		$main_filter->set_help('bignumber_show_filter', 'tac');
		$selection_filter = new Form_Field_Option_Model('selection_filter_id', 'With selection', $all_filters);
		$selection_filter->set_help('bignumber_with_selection', 'tac');
		$filters = new Form_Field_Group_Model('filters', array(
			$main_filter,
			$selection_filter
		));

		$uom = new Form_Field_Option_Model('display_type', 'Unit of measurement', array(
			'number_of_total' => 'Fraction',
			'number_only' => 'Count',
			'percent' => 'Percentage'
		));

		$toggle_status = new Form_Field_Boolean_Model('threshold_onoff', 'Show status');
		$threshold_as = new Form_Field_Option_Model('threshold_type', 'Threshold as', array(
			'less_than' => 'Less than',
			'greater_than' => 'Greater than',
		));
		$threshold_as->set_help('bignumber_threshold_as', 'tac');
		$thresholds = new Form_Field_Conditional_Model('threshold_onoff', true,
			new Form_Field_Group_Model("thresholds", array(
				$threshold_as,
				new Form_Field_Text_Model('threshold_warn', 'Warning threshold (%)'),
				new Form_Field_Text_Model('threshold_crit', 'Critical threshold (%)'),
			))
		);

		$form_model = Form_Model::for_tac_widget();
		foreach(array(
			$filters,
			$uom,
			$toggle_status,
			$thresholds
		) as $field) {
			$form_model->add_field($field);
		}

		$form_model->set_values($this->model->get_setting());

		return $form_model;
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
	 * @param $msg string
	 */
	private function error($msg) {
		$error_msg = $msg;
		require __DIR__.'/view_error.php';
	}

	/**
	 * Fetch the data and show the widget
	 */
	public function index() {
		$form_model = $this->options();

		// filters
		$main_set = $this->get_set_by_filter_id($form_model->get_value('main_filter_id', -200));
		$selection_set = $this->get_set_by_filter_id($form_model->get_value('selection_filter_id', -150));
		if($selection_set->get_table() !== $main_set->get_table()) {
			$msg = sprintf(
				"Your main filter is placed on the table '%s', but your selection filter is not. You need to filter on the same table.",
				$main_set->get_table()
			);
			return $this->error($msg);
		}
		$query = $main_set->intersect($selection_set)->get_query();
		$pool = $main_set->class_pool();
		$all_set = $pool::all();
		$counts = $main_set->stats(array(
			'all' => $all_set,
			'selection' => $selection_set
		));

		// thresholds
		$threshold_types = array();
		$threshold_types['less_than'] = function ($val, $stat) {
			return 100.0 * $stat['selection'] / $stat['all'] < $val;
		};
		$threshold_types['greater_than'] = function ($val, $stat) {
			return 100.0 * $stat['selection'] / $stat['all'] > $val;
		};
		$threshold_type = $form_model->get_value('threshold_type', 'less_than');
		$threshold_callback = $threshold_types[$threshold_type];

		// display
		if ($counts['all'] == 0) {
			// PHP is so bad, it cannot even divide by zero
			$state = 'pending';
			$display_explanation = 'No object matches this filter';
			$display_text = "";
		} else {
			switch($form_model->get_value('display_type', 'number_of_total')) {
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
			$display_explanation = "";

			if ($form_model->get_value('threshold_onoff', true)) {
				if (call_user_func_array($threshold_callback, array($form_model->get_value('threshold_crit', 90.0), $counts))) {
					$state = 'critical';
				} elseif (call_user_func_array($threshold_callback, array($form_model->get_value('threshold_warn', 95.0), $counts))) {
					$state = 'warning';
				} else {
					$state = 'ok';
				}
			} else {
				$state = 'info';
			}
		}

		require('view.php');
	}
}
