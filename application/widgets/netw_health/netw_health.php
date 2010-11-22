<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network health widget
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class Netw_health_Widget extends widget_Core {
	# define warning/critical limit
	private $health_warning_percentage = 90;
	private $health_critical_percentage = 75;
	private $host_img = false;
	private $service_img = false;
	private $crit_img = '/images/thermcrit.png';
	private $warn_img = '/images/thermwarn.png';
	private $ok_img = '/images/thermok.png';
	private $host_val = false;
	private $service_val = false;

	public function __construct()
	{
		parent::__construct();

		# needed to figure out path to widget
		$this->set_widget_name(__CLASS__, basename(__FILE__));
	}

	public function index($arguments=false, $master=false)
	{
		$this->master_obj = $master;

		# fetch widget view path
		$view_path = $this->view_path('view');

		# use first argument as reference to current_status object
		# this to prevent all widgets to fetch their own data
		# as this would slow down things drastically
		if (is_object($arguments[0])) {
			$current_status = $arguments[0];
			array_shift($arguments);
		} else {
			$current_status = new Current_status_Model();
		}

		if (!$current_status->data_present()) {
			$current_status->analyze_status_data();
		}

		# fetch network health data
		$this->host_val = $current_status->percent_host_health;
		$this->service_val = $current_status->percent_service_health;

		$this->health_warning_percentage =
			isset($arguments['health_warning_percentage'])
			? $arguments['health_warning_percentage']
			: $this->health_warning_percentage;

		$this->health_critical_percentage =
			isset($arguments['health_critical_percentage'])
			? $arguments['health_critical_percentage']
			: $this->health_critical_percentage;

		# format data according to current values
		$this->format_health_data();

		$health_warning_percentage = $this->health_warning_percentage;
		$health_critical_percentage = $this->health_critical_percentage;

		# assign variables to widget
		$widget_id = $this->widgetname;
		$refresh_rate = 60;
		if (isset($arguments['refresh_interval'])) {
			$refresh_rate = $arguments['refresh_interval'];
		}
		$title = $this->translate->_('Network health');
		if (isset($arguments['widget_title'])) {
			$title = $arguments['widget_title'];
		}

		$host_label = $this->translate->_('HOSTS');
		$service_label = $this->translate->_('SERVICES');
		$host_value 	= $this->host_val;
		$service_value 	= $this->service_val;
		$host_image 	= $this->widget_full_path.$this->host_img;
		$service_image 	= $this->widget_full_path.$this->service_img;

		# let view template know if wrapping div should be hidden or not
		$ajax_call = request::is_ajax() ? true : false;

		# set required extra resources
		$this->js = array('/js/netw_health');
		#$this->css = array('/css/netw_health');

		# fetch widget content
		require_once($view_path);

		if(request::is_ajax()) {
			# output widget content
			echo json::encode( $this->output());
		} else {
			# call parent helper to assign all
			# variables to master controller
			return $this->fetch();
		}
	}

	/**
	 * Decide how to present network health
	 *
	 */
	private function format_health_data()
	{
		# host bar color
		if ($this->host_val < $this->health_critical_percentage)
			$this->host_img = $this->crit_img;
		elseif ($this->host_val < $this->health_warning_percentage)
			$this->host_img = $this->warn_img;
		else
			$this->host_img = $this->ok_img;

		# service bar color
		if ($this->service_val < $this->health_critical_percentage)
			$this->service_img = $this->crit_img;
		elseif ($this->service_val < $this->health_warning_percentage)
			$this->service_img = $this->warn_img;
		else
			$this->service_img = $this->ok_img;
		return true;
	}
}