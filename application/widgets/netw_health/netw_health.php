<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network health widget
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
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
	private $data = false;

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

		# create new model instance
		$current_status = new Current_status_Model();

		# fetch network health data
		$this->data = $current_status->get_network_health();

		# format data according to current values
		$this->format_health_data();

		# assign variables to widget
		$host_value 	= $this->host_val;
		$service_value 	= $this->service_val;
		$host_image 	= $this->widget_full_path.$this->host_img;
		$service_image 	= $this->widget_full_path.$this->service_img;

		# set required extra resources
		$this->js = array('/js/netw_health');
		$this->css = array('/css/netw_health');

		# fetch widget content
		require_once($view_path);

		# call parent helper to assign all
		# variables to master controller
		return $this->fetch();
	}

	/**
	*	@name format_health_data
	*	@desc Decide how to present network health
	*
	*/
	private function format_health_data()
	{
		$data = $this->data;
		if (empty($data)) {
			return false;
		}

		$this->host_val = $data['host_status'];
		$this->service_val = $data['service_status'];

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