<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Search controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Search_Controller extends Authenticated_Controller {

	const FILTER_CHAR = ':';
	const DELIM_CHAR = ' and ';
	const SEPARATOR = ' or ';
	const LIMIT_STR = 'limit=';

	/**
	*	Provide search functionality for all object types
	*/
	public function lookup($query=false, $obj_type=false)
	{
		$obj_type = urldecode($this->input->get('obj_type', $obj_type));
		$query = urldecode($this->input->get('query', $query));

		# check if we have limit information
		# should be in the form of limit=100
		# or limit=0 which would disable the limit
		if (strstr($query, self::LIMIT_STR)) {
			$limit_part = explode(self::LIMIT_STR, $query);
			if (is_array($limit_part)) {
				$in_limit = $limit_part[1];
				$query = str_replace(self::LIMIT_STR.$in_limit, '', $query);
				$in_limit = (int)trim($in_limit);
				$query = trim($query);
			}
		}

		# Handle AND search
		# This will make it possible to search for services on specific hosts
		# example h:windows AND s:http would search for all services called
		# *http* on hosts called *windows*
		# By combining this with the OR search we can create searches
		# like: host:windows OR linux AND service:http OR web
		$valid_multiobj = array('h' => 'hosts', 'host' => 'hosts', 's' => 'services', 'service' => 'services');
		if (strstr(strtolower($query), self::DELIM_CHAR)) {
			$options = explode(self::DELIM_CHAR, strtolower($query));

			if (is_array($options)) {
				foreach ($options as $opt) {
					if (strstr($opt, self::FILTER_CHAR)) {
						$obj_parts = explode(self::FILTER_CHAR, $opt);
						if (is_array($obj_parts) && !empty($obj_parts) && array_key_exists($obj_parts[0], $valid_multiobj)) {
							$obj_type[$obj_parts[0]] = trim($obj_parts[1]);
						}
					}
				}
			}
		}

		if (!is_array(($obj_type)) ) {
			# regular search = only single object type (not an AND search)
			if (strstr($query, self::FILTER_CHAR)) {
				$options = explode(self::FILTER_CHAR, $query);
				if (is_array($options) && !empty($options[0])) {
					$obj_type = trim($options[0]);
					if (isset($options[1])) {
						$query = trim($options[1]);
					}
				}
			}

			switch ($obj_type) {
				case 'host': case 'h':
					$obj_type = 'host';
					$settings = array(
						'class' => 'Host_Model',
						'template' => 'status/service'
						);
					break;
				case 'service': case 's':
					$obj_type = 'service';
					$settings = array(
						'class' => 'Service_Model',
						'path' => 'extinfo/index'
					);
					break;
				case 'hostgroup': case 'hg':
					$obj_type = 'hostgroup';
					$settings = array(
						'class' => 'Hostgroup_Model',
						'path' => 'status/group_overview'
					);
					break;
				case 'servicegroup': case 'sg':
					$obj_type = 'servicegroup';
					$settings = array(
						'class' => 'Servicegroup_Model',
						'path' => 'status/group_overview'
					);
					break;
				default:
					$settings = false;
			}
		} else {
			$hosts = false;
			$services = false;
			foreach ($obj_type as $type => $obj) {
				if (strstr(strtolower($obj), self::SEPARATOR)) {
					$multi_obj = explode(self::SEPARATOR, strtolower($obj));
					foreach ($multi_obj as $m) {
						# assign found object to hosts or services array
						# depending on if 'h' or 's'
						${$valid_multiobj[$type]}[] = trim($m);
					}
				} else {
					${$valid_multiobj[$type]}[] = trim($obj);
				}
			}
		}

		$this->template->content = $this->add_view('search/result');
		$this->template->js_header = $this->add_view('js_header');
		$content = $this->template->content;
		$limit = !isset($in_limit) ? Kohana::config('config.search_limit') : $in_limit;
		$content->query = $query;
		$content->limit_str = !empty($limit)
			? sprintf($this->translate->_('Search result limited to %s rows'), $limit)
			:$this->translate->_('No search limit is active');
		$this->template->title = $this->translate->_('Search » ')."'".$query."'";

		/**
		 * Modify config/config.php to enable NACOMA
		 * and set the correct path in config/config.php,
		 * if installed, to use this
		 */
		if (nacoma::link()!==false) {
			$label_nacoma = $this->translate->_('Configure this object using NACOMA (Nagios Configuration Manager)');
			$content->nacoma_link = 'configuration/configure/';
			$content->label_nacoma = $label_nacoma;
		}

		# user requested a special object type
		if (!empty($settings)) {
			$obj_class_name = $settings['class'];
			$obj_class = new $obj_class_name();

			if (strstr(strtolower($query), self::SEPARATOR)) {
				$query = explode(self::SEPARATOR, strtolower($query)) ;
			}

			# find requested object
			$data = $obj_class->search($query, $limit);
			if ($data!==false) {
				$content->{$obj_type.'_result'} = $data;
			} else {
				$content->no_data = $this->translate->_('Nothing found');
			}
		} elseif ((isset($hosts) && !empty($hosts)) && (isset($services) && !empty($services)) ) {
			# AND search
			$obj_class = new Service_Model();
			$data = $obj_class->multi_search($hosts, $services, $limit);

			if ($data!==false) {
				$content->service_result = $data;
			} else {
				$content->no_data = $this->translate->_('Nothing found');
			}
		} else {
			# search through everything
			$objects = array('host' => 'Host_Model', 'service' => 'Service_Model', 'hostgroup' => 'Hostgroup_Model', 'servicegroup' => 'Servicegroup_Model');
			$empty = 0;
			if (strstr(strtolower($query), self::SEPARATOR)) {
				$query = explode(self::SEPARATOR, strtolower($query)) ;
			}

			foreach ($objects as $obj => $model) {
				$obj_class_name = $model;
				$obj_class = new $obj_class_name();
				$data = $obj_class->search($query, $limit);
				$obj_info = false;

				if (count($data) > 0 && $data !== false) {
					$content->{$obj.'_result'} = $data;
				} else {
					$empty++;
				}
			}
			if (!empty($empty) && $empty==4) {
				$content->no_data = $this->translate->_('Nothing found');
			}
		}
	}

	/**
	* Translated helptexts for this controller
	*/
	public static function _helptexts($id)
	{
		$translate = zend::instance('Registry')->get('Zend_Translate');

		# No helptexts defined yet - this is just an example
		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array(
			'search_help' => $translate->_("The search result is by default limited to 10 rows (for each object type).
					<br />Use limit=&lt;number&gt; (e.g limit=100) to change this or limit=0 to disable the limit entirely.<br /><br />
					You may also perform an AND search on hosts and services: 'h:web AND s:ping' will search for
					all services called something like ping on hosts called something like web.<br />
					Furthermore, it's possible to make OR searches: 'h:web OR mail' to search for hosts with web or mail
					in any of the searchable fields.<br />
					Combine AND with OR: 'h:web OR mail AND s:ping OR http'")
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		}
		else
			echo sprintf($translate->_("This helptext ('%s') is yet not translated"), $id);
	}
}
