<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network health widget
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class Netw_health_Widget extends widget_Base {
	protected $duplicatable = true;

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

	public function __construct($model)
	{
		parent::__construct($model);

		$this->health_warning_percentage =
			isset($this->model->setting['health_warning_percentage'])
			? $this->model->setting['health_warning_percentage']
			: $this->health_warning_percentage;

		$this->health_critical_percentage =
			isset($this->model->setting['health_critical_percentage'])
			? $this->model->setting['health_critical_percentage']
			: $this->health_critical_percentage;

	}

	public function options()
	{
		$options = parent::options();
		$options[] = new option($this->model->name, 'health_warning_percentage', 'Warning Percentage Level', 'input', array(
			'style' => 'width:20px',
			'title' => sprintf(_('Default value: %s%%'), 90)), $this->health_warning_percentage);
		$options[] = new option($this->model->name, 'health_critical_percentage', 'Critical Percentage Level', 'input', array(
			'style' => 'width:20px',
			'title' => sprintf(_('Default value: %s%%'), 75)), $this->health_warning_percentage);
		return $options;
	}

	public function index()
	{
		# fetch widget view path
		$view_path = $this->view_path('view');
		$current_status = $this->get_current_status();

		# fetch network health data
		$this->host_val = $current_status->percent_host_health;
		$this->service_val = $current_status->percent_service_health;

		# format data according to current values
		$this->format_health_data();

		$health_warning_percentage = $this->health_warning_percentage;
		$health_critical_percentage = $this->health_critical_percentage;

		$host_label = _('HOSTS');
		$service_label = _('SERVICES');
		$host_value 	= $this->host_val;
		$service_value 	= $this->service_val;
		$host_image 	= $this->widget_full_path.$this->host_img;
		$service_image 	= $this->widget_full_path.$this->service_img;

		# set required extra resources
		$this->js = array('js/netw_health');
		require($view_path);
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
