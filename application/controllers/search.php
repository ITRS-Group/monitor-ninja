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
	public $xtra_query = false;

	/**
	*	Provide search functionality for all object types
	*/
	public function lookup($query=false, $obj_type=false)
	{
		$obj_type = urldecode($this->input->get('obj_type', $obj_type));

		$host = urldecode(trim($this->input->get('host', false)));
		$hostgroup = urldecode(trim($this->input->get('hostgroup', false)));
		$service = urldecode(trim($this->input->get('service', false)));
		$servicegroup = urldecode(trim($this->input->get('servicegroup', false)));
		$comment = urldecode(trim($this->input->get('comment', false)));
		$status = urldecode(trim($this->input->get('status', false)));

		$host = !empty($host) ? 'h:'.$host : false;
		$hostgroup = !empty($hostgroup) ? 'hg:'.$hostgroup : false;
		$service = !empty($service) ? 's:'.$service : false;
		$servicegroup = !empty($servicegroup) ? 'sg:'.$servicegroup : false;
		$comment = !empty($comment) ? 'c:'.$comment : false;
		$status = !empty($status) ? 'si:'.$status : false;

		# join all search fields into query
		$queries = false;
		$queries[] = $host;
		$queries[] = $hostgroup;
		$queries[] = $service;
		$queries[] = $servicegroup;
		$queries[] = $comment;
		$queries[] = $status;

		foreach ($queries as $q) {
			if (!empty($q)) {
				$query .= $q.' ';
			}
		}

		$query = $this->input->get('query', $query);
		$objects = array('host' => 'Host_Model', 'service' => 'Service_Model', 'hostgroup' => 'Hostgroup_Model', 'servicegroup' => 'Servicegroup_Model', 'comment' => 'Comment_Model');
		$query_str = trim($query); # stash query string for later use when saving search

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
		$valid_multiobj = array('h' => 'hosts', 'host' => 'hosts', 's' => 'services', 'service' => 'services', 'c' => 'comment', 'comment' => 'comment');
		$extra_params = array('si', 'status');
		if (strstr(strtolower($query), self::DELIM_CHAR)) { # AND => host AND service/status information
			$options = explode(self::DELIM_CHAR, strtolower($query));

			if (is_array($options)) {
				foreach ($options as $opt) {
					$tmp_obj = false;
					if (strstr($opt, self::FILTER_CHAR)) { # :
						if (strstr($opt, self::SEPARATOR)) { # or
							$parts = explode(self::SEPARATOR, $opt);
							foreach ($parts as $p) {
								if (strstr($p, self::FILTER_CHAR)) {
									$filter_p = explode(self::FILTER_CHAR, $p);
									if (is_array($filter_p) && !empty($filter_p) && array_key_exists($filter_p[0], $valid_multiobj)) {
										$obj_type[$filter_p[0]][] = trim($filter_p[1]);
										$tmp_obj = $filter_p[0];
									}
								} elseif(!empty($tmp_obj) && array_key_exists($tmp_obj, $valid_multiobj)) {
									$obj_type[$tmp_obj][] = trim($p);
								}
							}
						} else {
							$parts = explode(self::FILTER_CHAR, $opt);

							if (is_array($parts) && !empty($parts) && array_key_exists($parts[0], $valid_multiobj)) {
								$obj_type[$parts[0]][] = trim($parts[1]);
							} elseif (is_array($parts) && in_array($parts[0], $extra_params)) {
								# detected extra parameter (like si/status). Stash this
								$xtra_query = trim($parts[1]);
								if (strstr($xtra_query, self::SEPARATOR)) {
									$this->xtra_query = explode(self::SEPARATOR, $xtra_query);
								} else {
									$this->xtra_query = array($xtra_query);
								}
							}
						}
					}
				}
			}
		}

		$or_search = false;
		if (!is_array($obj_type) && strstr($query, self::SEPARATOR)) { # OR
			# OR detected
			$or_search = true;

			$tmp_obj = false;
			$options = explode(self::SEPARATOR, $query);
			if (is_array($options) && !empty($options)) {
				foreach ($options as $opt) {
					if (strstr($opt, self::FILTER_CHAR)) {
						$parts = explode(self::FILTER_CHAR, $opt);
						if (is_array($parts) && sizeof($parts) == 2 && array_key_exists($parts[0], $valid_multiobj)) {
							# only host or service objects supported
							$tmp_obj = $parts[0];
							$obj_type[$parts[0]][] = trim($parts[1]);
						}
					} elseif (!empty($tmp_obj) && array_key_exists($tmp_obj, $valid_multiobj)) {
						$obj_type[$tmp_obj][] = trim($opt);
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
				case 'comment': case 'c':
					$obj_type = 'comment';
					$settings = array(
						'class' => 'Comment_Model',
						'path' => 'extinfo/show_comments'
					);
					break;
				case 'si':

				default:
					$settings = false;
			}
		} else {
			$hosts = false;
			$services = false;
			foreach ($obj_type as $type => $obj) {
				if (is_array($obj)) {
					foreach ($obj as $o) {
						if (strstr(strtolower($o), self::SEPARATOR)) {
							$multi_obj = explode(self::SEPARATOR, strtolower($o));
							foreach ($multi_obj as $m) {
								# assign found object to hosts or services array
								# depending on if 'h' or 's'
								${$valid_multiobj[$type]}[] = trim($m);
							}
						} else {
							${$valid_multiobj[$type]}[] = trim($o);
						}
					}
				} else {
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
			if (isset($obj_type['h'])) {
				$hosts = $obj_type['h'];
			}
			if (isset($obj_type['s'])) {
				$services = $obj_type['s'];
			}

		}

		$this->template->content = $this->add_view('search/result');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->template->css_header->css = $this->xtra_css;

		$content = $this->template->content;
		$content->date_format_str = nagstat::date_format();
		$limit = !empty($in_limit) ? $in_limit : false;
		$items_per_page = urldecode($this->input->get('items_per_page', false));
		$custom_limit = urldecode($this->input->get('custom_pagination_field', false));
		$limit = empty($limit) ? config::get('pagination.default.items_per_page', '*') : $limit;
		$items_per_page = !empty($custom_limit) ? $custom_limit : $items_per_page;

		foreach ($objects as $obj => $discard) {
			${$obj.'_items_per_page'} =  $limit;
		}

		$result_type = $this->input->get('result', false);

		if (!empty($result_type)) {
			${$result_type.'_items_per_page'} = !empty($items_per_page) ? $items_per_page : $limit;
		}

		$pagination_type = 'punbb';
		$content->query = $query;
		$this->template->query_str = $query_str;
		$content->limit_str = !empty($limit)
			? sprintf($this->translate->_('Search result limited to %s rows'), $limit)
			:$this->translate->_('No search limit is active');
		$this->template->title = $this->translate->_('Search » ')."'".$query."'";

		$content->show_display_name = config::get('config.show_display_name', '*');
		$content->show_notes = config::get('config.show_notes', '*');


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

			${$obj_type.'_pagination'} = false;
			$tot = false;
			# find requested object
			if (!empty($limit)) {
				$data_cnt = $obj_class->search($query, 0);
				$tot = count($data_cnt);
				${$obj_type.'_pagination'} = new Pagination(
					array(
						'total_items'=> $tot,
						'style' => $pagination_type,
						'items_per_page' => ${$obj_type.'_items_per_page'},
						'query_string' => $obj_type.'_page'
					)
				);
				$offset = ${$obj_type.'_pagination'}->sql_offset;
				$limit = !empty($offset) ? $offset.','.$limit : $limit;
			}

			$content->{$obj_type.'_pagination'} = ${$obj_type.'_pagination'};
			$data = $obj_class->search($query, $limit);
			if ($data!==false) {
				$content->{$obj_type.'_result'} = $data;
			} else {
				$content->no_data = $this->translate->_('Nothing found');
			}
		} elseif ((isset($hosts) && !empty($hosts)) && (isset($services) && !empty($services)) && $or_search !== true) {
			# AND search
			$service_pagination = false;
			$tot = false;
			# find requested object
			$obj_class = new Service_Model();
			if (!empty($limit)) {
				$data_cnt = $obj_class->multi_search($hosts, $services, $this->xtra_query, 0);
				$tot = count($data_cnt);
				$service_pagination = new Pagination(
					array(
						'total_items'=> $tot,
						'style' => $pagination_type,
						'items_per_page' => $service_items_per_page,
						'query_string' => 'service_page'
					)
				);
				$offset = $service_pagination->sql_offset;
				$limit = !empty($offset) ? $offset.','.$limit : $limit;
				$content->service_pagination = $service_pagination;
			}
			$data = $obj_class->multi_search($hosts, $services, $this->xtra_query, $limit);

			if ($data!==false) {
				$content->service_result = $data;
			} else {
				$content->no_data = $this->translate->_('Nothing found');
			}
		} elseif ( ((isset($hosts) && !empty($hosts)) || ( (isset($services) && !empty($services)) ))
			&& ( !empty($this->xtra_query) || $or_search === true ) ) {
			# we end up here when searching for hosts OR services and
			# an extra param like si/status was supplied OR we are performing an OR search
			$empty = 0;
			$cnt = 0;
			if ( isset($hosts) && !empty($hosts) ) {
				$hmodel = new Host_Model();
				$data = $hmodel->search($hosts, $limit, $this->xtra_query);
				if (count($data) > 0 && $data !== false) {
					$content->host_result = $data;
				} else {
					$empty++;
				}
			}
			if ( isset($services) && !empty($services) ) {
				$Smodel = new Service_Model();
				$data = $Smodel->search($services, $limit, $this->xtra_query);
				if (count($data) > 0 && $data !== false) {
					$content->service_result = $data;
				} else {
					$empty++;
				}
			}
			if (!empty($empty)) {
				$content->no_data = $this->translate->_('Nothing found');
			}

		} elseif ($or_search === true && isset($obj_type) && !empty($obj_type) && !isset($settings)) {
			# or search - try to figure out object type
			$empty = 0;
			$obj_class = false;
			$value = false;
			foreach ($obj_type as $o => $parts) {
				if (array_key_exists($o, $valid_multiobj)) {
					$obj_class[] = $valid_multiobj[$o];
					$value[$valid_multiobj[$o]] = $parts;
				}
			}
			if (!empty($obj_class) && !empty($value)) {
				foreach ($obj_class as $class_name) {
					$objclass_name = ucfirst($class_name).'_Model';
					$obj = new $objclass_name;

					if (!empty(${$class_name.'_items_per_page'})) {
						${$class_name.'_limit'} = $limit;
						$data_cnt = $obj->search($value[$class_name], 0);
						$tot = count($data_cnt);
						unset($data_cnt);
						${$class_name.'_pagination'} = new Pagination(
							array(
								'total_items'=> $tot,
								'items_per_page' => ${$class_name.'_items_per_page'},
								'style'          => $pagination_type,
								'query_string' => $class_name.'_page'
							)
						);

						$offset = ${$class_name.'_pagination'}->sql_offset;
						${$class_name.'_limit'} = !empty($offset) ? $offset.','.${$class_name.'_items_per_page'} : ${$class_name.'_items_per_page'};
						$content->{$class_name.'_pagination'} = ${$class_name.'_pagination'};
					} else {
						${$class_name.'_limit'} = $limit;
					}
					$data = $obj->search($value[$class_name], ${$class_name.'_limit'});
					if (count($data) > 0 && $data !== false) {
						$content->{$class_name.'_result'} = $data;
					} else {
						$empty++;
					}
					unset($data);
				}
			}
		} else {
			# search through everything
			$empty = 0;
			if (strstr(strtolower($query), self::SEPARATOR)) {
				$query = explode(self::SEPARATOR, strtolower($query)) ;
			}

			foreach ($objects as $obj => $model) {
				$obj_class_name = $model;
				$obj_class = new $obj_class_name();
				if (!empty(${$obj.'_items_per_page'})) {
					${$obj.'_limit'} = $limit;
					$data_cnt = $obj_class->search($query, 0);
					$tot = count($data_cnt);
					unset($data_cnt);
					${$obj.'_pagination'} = new Pagination(
						array(
							'total_items'=> $tot,
							'items_per_page' => ${$obj.'_items_per_page'},
							'style'          => $pagination_type,
							'query_string' => $obj.'_page'
						)
					);

					$offset = ${$obj.'_pagination'}->sql_offset;
					${$obj.'_limit'} = !empty($offset) ? $offset.','.${$obj.'_items_per_page'} : ${$obj.'_items_per_page'};
					$content->{$obj.'_pagination'} = ${$obj.'_pagination'};
				} else {
					${$obj.'_limit'} = $limit;
				}

				$data = $obj_class->search($query, ${$obj.'_limit'});
				$obj_info = false;

				if (count($data) > 0 && $data !== false) {
					$content->{$obj.'_result'} = $data;
				} else {
					$empty++;
				}
				unset($data);
			}
			if (!empty($empty) && $empty==5) {
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

		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array(
			'search_help' => sprintf($translate->_("You may perform an AND search on hosts and services: 'h:web AND s:ping' will search for	all services called something like ping on hosts called something like web.<br /><br />
			Furthermore, it's possible to make OR searches: 'h:web OR mail' to search for hosts with web or mail in any of the searchable fields.<br /><br />
			Combine AND with OR: 'h:web OR mail AND s:ping OR http'<br /><br />
			Use si:critical to search for status information like critical<br /><br />
			Read the manual for more tips on searching.<br /><br />

			The search result is currently limited to %s rows (for each object type).<br /><br />
			To temporarily change this for your search, use limit=&lt;number&gt; (e.g limit=100) or limit=0 to disable the limit entirely."), config::get('pagination.default.items_per_page', '*')
			),
			'saved_search_help' => $translate->_('Click to save this search for later use. Your saved searches will be available by clicking on the icon just below the search field at the top of the page.')
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		}
		else
			echo sprintf($translate->_("This helptext ('%s') is yet not translated"), $id);
	}
}
