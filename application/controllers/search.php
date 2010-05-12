<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Search controller
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 * @copyright 2009 op5 AB
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

	/**
	*	Provide search functionality for all object types
	*/
	public function lookup($query=false, $obj_type=false)
	{
		$obj_type = urldecode($this->input->get('obj_type', $obj_type));
		$query = urldecode($this->input->get('query', $query));
		if (strstr($query, self::FILTER_CHAR)) {
			$options = explode(self::FILTER_CHAR, $query);
			if (is_array($options) && !empty($options[0])) {
				$obj_type = trim($options[0]);
				if (isset($options[1])) {
					$query = $options[1];
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

		$this->template->content = $this->add_view('search/result');
		$this->template->js_header = $this->add_view('js_header');
		$content = $this->template->content;
		$limit = Kohana::config('config.search_limit');
		$content->query = $query;
		$this->template->title = $this->translate->_('Search » ')."'".$query."'";
		# user requested a special object type
		if (!empty($settings)) {
			$obj_class_name = $settings['class'];
			$obj_class = new $obj_class_name();
			# find requested object

			$data = $obj_class->search($query, $limit);
			if ($data!==false) {
				$content->{$obj_type.'_result'} = $data;
			} else {
				$content->no_data = $this->translate->_('Nothing found');
			}
		} else {
			# search through everything
			$objects = array('host' => 'Host_Model', 'service' => 'Service_Model', 'hostgroup' => 'Hostgroup_Model', 'servicegroup' => 'Servicegroup_Model');
			$empty = 0;
			foreach ($objects as $obj => $model) {
				$obj_class_name = $model;
				$obj_class = new $obj_class_name();
				$data = $obj_class->search($query, $limit);
				$obj_info = false;
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


				if (count($data) > 0 && $data !== false) {
					$content->{$obj.'_result'} = $data;
				} else {
					$empty++;
				}
			}
		}
		if (!empty($empty) && $empty==4) {
			$content->no_data = $this->translate->_('Nothing found');
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
