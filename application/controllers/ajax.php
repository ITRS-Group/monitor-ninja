<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controller to fetch data via Ajax calls
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Ajax_Controller extends Authenticated_Controller {

	const FILTER_CHAR = ':';
	const DELIM_CHAR = ':';

	public function __construct()
	{
		parent::__construct();
		if ($this->profiler)
			$this->profiler->disable();
		$this->auto_render=false;
	}

	/**
	*	Handle search queries from front page search field
	*/
	public function global_search($q=false)
	{
		if(!request::is_ajax()) {
			$msg = $this->translate->_('Only Ajax calls are supported here');
			die($msg);
		} else {
			# we handle queries by trying to locate wanted filtering options separated by colon (:)
			# supporting both GET and POST here
			$q = $this->input->get('query', $q);
			$q = urldecode($q);
			$json = zend::instance('json');
			if (strstr($q, self::FILTER_CHAR)) {
				# some extra filtering option detected
				$options = explode(self::FILTER_CHAR, $q);
				$valid_objects = array(
					'host' => array(
						'class' => 'Host_Model',
						'name_field' => 'host_name',
						'data' => 'host_name',
						'path' => '/status/service/%s'
						),
					'service' => array(
						'class' => 'Service_Model',
						'name_field' => 'service_description',
						'data' => 'host_name',
						'path' => '/extinfo/details/service/%s/?service=%s'
					),
					'hostgroup' => 'Hostgroup_Model',
					'servicegroup' => 'Servicegroup_Model'
					);
				$obj_type = false;
				$obj_class_name = false;
				$obj_class = false;
				$obj_name = false;
				$obj_data = false;
				$obj_info = false;
				if (is_array($options)) {
					$obj_type = trim($options[0]);
					if (!array_key_exists($obj_type, $valid_objects)) {
						return false;
					}
					$obj_class_name = $valid_objects[$obj_type]['class'];
					$obj_class = new $obj_class_name();
					# find requested object
					if (isset($options[1])) {
						$obj_name = $options[1];
					}
					$limit = 10; # limit search result to max items returned
					$data = $obj_class->get_where($valid_objects[$obj_type]['name_field'], $obj_name, $limit);
					$obj_info = false;
					if ($data!==false) {
						foreach ($data as $row) {
							$obj_info[] = $obj_type == 'service' ? $row->{$valid_objects[$obj_type]['data']} . ';' . $row->{$valid_objects[$obj_type]['name_field']} : $row->{$valid_objects[$obj_type]['name_field']};
							$obj_data[] = array($valid_objects[$obj_type]['path'], $row->{$valid_objects[$obj_type]['data']});
						}
					} else {
						$host_info = 'Nothing found';
					}
					$var = array('query' => $q, 'suggestions' => $obj_info, 'data' => $obj_data);
					$json_str = $json->encode($var);
					echo $json_str;

				} else {
					return false;
				}
			} else {
				# assuming we want host data
				$host_model = new Host_Model();
				$limit = 10; # limit search result to max items returned
				$data = $host_model->get_where('host_name', $q, $limit);
				$host_info = false;
				if ($data!==false) {
					foreach ($data as $row) {
						$host_info[] = $row->host_name;
						$host_data[] = array('/status/service/%s', $row->host_name);
					}
				} else {
					$host_info = 'Nothing found';
				}
				$var = array('query' => $q, 'suggestions' => $host_info, 'data' => $host_data);
				$json_str = $json->encode($var);
				echo $json_str;
			}
		}
	}
}