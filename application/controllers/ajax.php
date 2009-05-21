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
			$q = $this->input->get('query', $q);
			$q = urldecode($q);
			$json = zend::instance('json');
			if (strstr($q, self::FILTER_CHAR)) {
				# some extra filtering option detected
				$options = explode(self::FILTER_CHAR, $q);
				$obj_type = false;
				$obj_class_name = false;
				$obj_class = false;
				$obj_name = false;
				$obj_data = false;
				$obj_info = false;
				if (is_array($options) && !empty($options[0])) {
					$obj_type = trim($options[0]);
					if (isset($options[1])) {
						$obj_name = $options[1];
					} else {
						return false;
					}
					switch ($obj_type) {
						case 'host': case 'h':
							$settings = array(
								'class' => 'Host_Model',
								'name_field' => 'host_name',
								'data' => 'host_name',
								'path' => '/status/service/%s'
								);
							break;
						case 'service': case 's':
							$obj_type = 'service';
							$settings = array(
								'class' => 'Service_Model',
								'name_field' => 'service_description',
								'data' => 'host_name',
								'path' => '/extinfo/details/service/%s/?service=%s'
							);
							break;
						case 'hostgroup': case 'hg':
							$settings = array(
								'class' => 'Hostgroup_Model',
								'name_field' => 'hostgroup_name',
								'data' => 'hostgroup_name',
								'path' => '/status/hostgroup/%s'
							);
							break;
						case 'servicegroup': case 'sg':
							$settings = array(
								'class' => 'Servicegroup_Model',
								'name_field' => 'servicegroup_name',
								'data' => 'servicegroup_name',
								'path' => '/status/servicegroup/%s'
							);
							break;
						default:
							return false;
					}
					$obj_class_name = $settings['class'];
					$obj_class = new $obj_class_name();
					# find requested object
					$limit = 10; # limit search result to max items returned @@@FIXME should be configurable?
					$data = $obj_class->get_where($settings['name_field'], $obj_name, $limit);
					$obj_info = false;
					if ($data!==false) {
						foreach ($data as $row) {
							$obj_info[] = $obj_type == 'service' ? $row->{$settings['data']} . ';' . $row->{$settings['name_field']} : $row->{$settings['name_field']};
							$obj_data[] = array($settings['path'], $row->{$settings['data']});
						}
					} else {
						$host_info = $this->translate->_('Nothing found');
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
					$host_info = $this->translate->_('Nothing found');
				}
				$var = array('query' => $q, 'suggestions' => $host_info, 'data' => $host_data);
				$json_str = $json->encode($var);
				echo $json_str;
			}
		}
	}
}