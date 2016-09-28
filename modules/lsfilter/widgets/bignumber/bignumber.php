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

	/**
	 * @return string
	 */
	protected function get_suggested_title () {
		$form_model = $this->options();
		$content_from = $form_model->get_value('content_from', 'filter');
		if($content_from == 'filter') {
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
		} elseif($content_from == 'host') {
			$host = $form_model->get_value('host');
			if($host) {
				return sprintf(
					'Host %s: %s',
					$host->get_name(),
					$form_model->get_value('host_performance_data_source')
				);
			}
		} elseif($content_from == 'service') {
			$service = $form_model->get_value('service');
			if($service) {
				return sprintf(
					'Service %s: %s',
					$service->get_readable_name(),
					$form_model->get_value('service_performance_data_source')
				);
			}
		}
		return parent::get_suggested_title();
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

		$content_from = new Form_Field_Option_Model('content_from', 'Content from', array(
			'filter' => 'Filter',
			'host' => 'Host',
			'service' => 'Service',
		), $force_render = 'select');


		$stored_settings = $this->model->get_setting();
		$host_perfdata_sources = array();
		if(isset($stored_settings['host']) && $stored_settings['host']) {
			$host_model = HostPool_Model::fetch_by_key($stored_settings['host']['value']);
			if($host_model) {
				$host_perfdata_sources = array_keys($host_model->get_perf_data());
			}
		}
		$host = new Form_Field_Conditional_Model(
			'content_from',
			'host',
			new Form_Field_Group_Model(
				'host',
				array(
					new Form_Field_ORMObject_Model('host', 'Host name', array('hosts')),
					new Form_Field_Perfdata_Model('host_performance_data_source', 'Performance data', 'host', $host_perfdata_sources)
				)
			)
		);

		$service_perfdata_sources = array();
		if(isset($stored_settings['service']) && $stored_settings['service']) {
			$service_model = ServicePool_Model::fetch_by_key($stored_settings['service']['value']);
			if($service_model) {
				$service_perfdata_sources = array_keys($service_model->get_perf_data());
			}
		}
		$service = new Form_Field_Conditional_Model(
			'content_from',
			'service',
			new Form_Field_Group_Model(
				'service',
				array(
					new Form_Field_ORMObject_Model('service', 'Service description', array('services')),
					new Form_Field_Perfdata_Model('service_performance_data_source', 'Performance data', 'service', $service_perfdata_sources)
				)
			)
		);

		$main_filter = new Form_Field_Option_Model('main_filter_id', 'Show filter', $all_filters);
		$main_filter->set_help('bignumber_show_filter', 'tac');
		$selection_filter = new Form_Field_Option_Model('selection_filter_id', 'With selection', $all_filters);
		$selection_filter->set_help('bignumber_with_selection', 'tac');
		$filters = new Form_Field_Conditional_Model(
			'content_from',
			'filter',
			new Form_Field_Group_Model('filters', array(
				$main_filter,
				$selection_filter
			))
		);

		$uom = new Form_Field_Conditional_Model(
			'content_from',
			'filter',
			new Form_Field_Option_Model('display_type', 'Unit of measurement', array(
				'number_of_total' => 'Fraction',
				'number_only' => 'Count',
				'percent' => 'Percentage'
			))
		);

		$toggle_status = new Form_Field_Boolean_Model('threshold_onoff', 'Color widget based on thresholds');
		$threshold_as = new Form_Field_Option_Model('threshold_type', 'Threshold as', array(
			'less_than' => 'Less than',
			'greater_than' => 'Greater than',
		));
		$threshold_as->set_help('bignumber_threshold_as', 'tac');
		$thresholds = new Form_Field_Conditional_Model('threshold_onoff', true,
				new Form_Field_Conditional_Model('content_from', 'filter',
				new Form_Field_Group_Model("thresholds", array(
					$threshold_as,
					new Form_Field_Text_Model('threshold_warn', 'Warning threshold (%)'),
					new Form_Field_Text_Model('threshold_crit', 'Critical threshold (%)'),
				))
			)
		);

		$regular_widget_form_fields = array(
			new Form_Field_Group_Model('meta', array(
				new Form_Field_Text_Model('title', 'Custom title'),
				new Form_Field_Number_Model('refresh_interval', 'Refresh (sec)'),
			))
		);

		$form_model = new Form_Model('widget/save_widget_setting',
			$regular_widget_form_fields);

		$form_model->add_button(new Form_Button_Confirm_Model('save', 'Save'));
		$form_model->add_button(new Form_Button_Cancel_Model('cancel', 'Cancel'));

		foreach(array(
			$content_from,
			$host,
			$service,
			$filters,
			$uom,
			$toggle_status,
			$thresholds
		) as $field) {
			$form_model->add_field($field);
		}

		$defaults = array(
			'threshold_type' => 'less_than',
			'content_from' => 'filter',
			'main_filter_id' => -200,
			'selection_filter_id' => -150,
			'selection_filter_id' => -150,
			'threshold_onoff' => true,
			'threshold_crit' => 90.0,
			'threshold_warn' => 95.0,
			'display_type' => 'number_of_total',
		);
		$settings = array_merge($defaults, $stored_settings);
		if(isset($settings['host']) && is_array($settings['host'])) {
			$host = HostPool_Model::fetch_by_key($settings['host']['value']);
			if($host) {
				$settings['host'] = $host;
			} else {
				unset($settings['host']);
			}
		}
		if(isset($settings['service']) && is_array($settings['service'])) {
			$service = ServicePool_Model::fetch_by_key($settings['service']['value']);
			if($service) {
				$settings['service'] = $service;
			} else {
				unset($settings['service']);
			}
		}

		$form_model->set_values($settings);
		$form_model->set_missing_fields_cb(array('title' => '', 'refresh_interval' => ''));
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

		$perf_data = array();
		$display_explanation = "";
		$display_text = "";
		$content_from = $form_model->get_value('content_from', 'filter');
		$link = '';
		$linkprovider = LinkProvider::factory();
		$state = 'info';
		if($content_from == 'filter') {
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
			$link = listview::querylink($query);
			$pool = $main_set->class_pool();
			$all_set = $pool::all();
			$counts = $main_set->stats(array(
				'all' => $all_set,
				'selection' => $selection_set
			));

			if ($counts['all'] == 0) {
				// PHP is so bad, it cannot even divide by zero
				$state = 'pending';
				$display_explanation = 'No object matches this filter';
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

				if ($form_model->get_value('threshold_onoff')) {
					$threshold_types = array();
					$threshold_types['less_than'] = function ($val, $stat) {
						return 100.0 * $stat['selection'] / $stat['all'] < $val;
					};
					$threshold_types['greater_than'] = function ($val, $stat) {
						return 100.0 * $stat['selection'] / $stat['all'] > $val;
					};
					$threshold_callback = $threshold_types[$form_model->get_value('threshold_type', 'less_than')];
					if (call_user_func_array($threshold_callback, array(
						$form_model->get_value('threshold_crit', 90.0),
						$counts))) {
						$state = 'critical';
					} elseif (call_user_func_array($threshold_callback, array(
						$form_model->get_value('threshold_warn', 95.0),
						$counts))) {
						$state = 'warning';
					} else {
						$state = 'ok';
					}
				}
			}
			require 'view.php';
		} elseif(in_array($content_from, array('host', 'service'), true)) {
			$perf_data_src = $form_model->get_value($content_from.'_performance_data_source');
			$object = $form_model->get_value($content_from);
			if(!$object) {
				$display_explanation = 'No perfdata for these settings';
				require 'view.php';
				return;
			}
			if($content_from === 'host') {
				$link = $linkprovider->get_url('extinfo', 'details', array(
					'host' => $object->get_name()
				));
			} elseif($content_from === 'service') {
				$link = $linkprovider->get_url('extinfo', 'details', array(
					'host' => $object->get_host()->get_name(),
					'service' => $object->get_description()
				));
			}
			$perf_data = $object->get_perf_data();
			if(!isset($perf_data[$perf_data_src]) || !isset($perf_data[$perf_data_src]['value'])) {
				$display_explanation = 'No perfdata for these settings';
				require 'view.php';
				return;
			}
			$perf_data = $perf_data[$perf_data_src];
			if($form_model->get_value('threshold_onoff') && isset($perf_data['warn'], $perf_data['crit'])) {
				if(performance_data::match_threshold($perf_data['crit'], $perf_data['value'])) {
					$state = 'critical';
				} else if(performance_data::match_threshold($perf_data['warn'], $perf_data['value'])) {
					$state = 'warning';
				} else {
					$state = 'ok';
				}
			}
			require 'view.php';
		}
	}
}
