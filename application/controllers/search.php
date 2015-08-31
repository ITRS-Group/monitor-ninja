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
class Search_Controller extends Ninja_Controller {
	/**
	 * Contains a list of columns to search in, depending on table.
	 *
	 * @var array of arrays.
	 */
	protected $search_columns = array(
		'hosts' => array( 'name', 'display_name', 'address', 'alias', 'notes' ),
		'services' => array( 'description', 'display_name', 'notes' ),
		'hostgroups' => array( 'name', 'alias' ),
		'servicegroups' => array( 'name', 'alias' ),
		'comments' => array( 'author', 'comment' ),
		'_si' => array('plugin_output', 'long_plugin_output')
	);

	protected $search_columns_matchall = array(
		'hosts' => array( 'name', 'display_name', 'address', 'alias', 'plugin_output', 'long_plugin_output', 'notes' ),
		'services' => array( 'description', 'display_name', 'host.name', 'host.address', 'host.alias', 'plugin_output', 'long_plugin_output', 'notes' ),
		'hostgroups' => array( 'name', 'alias' ),
		'servicegroups' => array( 'name', 'alias' ),
		'comments' => array( 'author', 'comment' )
	);

	protected $object_types = array(
			'h'  => 'hosts',
			's'  => 'services',
			'c'  => 'comments',
			'hg' => 'hostgroups',
			'sg' => 'servicegroups',
			'si' => '_si'
			);

	public function __construct()
	{
		parent::__construct();
		$global_search_tables = Module_Manifest_Model::get('global_search_tables');

		if(isset($global_search_tables['search_columns'])) {
			$this->search_columns = array_merge($this->search_columns, $global_search_tables['search_columns']);
		}

		if(isset($global_search_tables['search_columns_matchall'])) {
			$this->search_columns_matchall = array_merge($this->search_columns_matchall, $global_search_tables['search_columns_matchall']);
		}

		if(isset($global_search_tables['object_types'])) {
			$this->object_types = array_merge($this->object_types, $global_search_tables['object_types']);
		}

		// Disable full-page refresh
		$this->template->disable_refresh = true;
	}

	/**
	 * Do a search of a string
	 * (actually, call index...)
	 *
	 * @param $query search string
	*/
	public function lookup($query=false) {
		return $this->index($query);
	}

	/**
	 * Do a search of a string
	 *
	 * @param $query search string
	 */
	public function index($query=false) {
		$this->_verify_access('ninja.search:read.search');

		$original_query = $query = trim($this->input->get('query', $query));

		/* Is the query a complete search filter? */
		if(preg_match('/^\[[a-zA-Z]+\]/', $query)) {
			return url::redirect('listview?'.http_build_query(array('q'=>$query)));
		}

		/* Is the query a saved filter name? */
		$filters = LSFilter_Saved_Queries_Model::get_query($query);
		if($filters !== false) {
			return url::redirect('listview?'.http_build_query(array('q'=>$filters)));
		}

		/* Is the query a oldschool search filter? h:kaka or boll */
		$filters = $this->_queryToLSFilter( $query );

		/* Fallback on match everything */
		if($filters === false) {
			$filters = $this->_queryToLSFilter_MatchAll( $query );
		}

		if(count($filters)==1) {
			return url::redirect('listview?'.http_build_query(array('q'=>reset($filters))));
		}

		$limit = false;
		if(isset($filters['limit'])) {
			$limit = $filters['limit'];
			unset($filters['limit']);
		}

		$this->render_queries( $filters, $original_query, $limit );
	}

	/**
	 * Render a list of queries as a page containing listview widgets
	 *
	 * @param $queries list of queries
	 */
	private function render_queries($queries, $original_query, $limit=false) {
		if( !is_array($queries) ) {
			$queries = array($queries);
		}

		$this->template->content         = $this->add_view('search/result');

		$content = $this->template->content;
		$content->date_format_str = date::date_format();

		$this->template->content->widgets = array();

		$username = Auth::instance()->get_user()->username;

		if( $limit === false ) {
			$limit = config::get('pagination.default.items_per_page', '*');
		}
		foreach( $queries as $table => $query ) {
			$set = ObjectPool_Model::get_by_query($query);
			/* @var $set ObjectSet_Model */
			if($this->mayi->run($set->mayi_resource() . ':read.search')) {
				$setting = array('query'=>$query);
				$setting['limit'] = $limit;
				$model = new Ninja_widget_Model(array(
					'page' => Router::$controller,
					'name' => 'listview',
					'widget' => 'listview',
					'username' => $username,
					'friendly_name' => ucfirst($table),
					'setting' => $setting
				));

				$widget = widget::get($model, $this);
				widget::set_resources($widget, $this);

				$widget->set_fixed();
				// abuse the fact that ls-tables are pluralized
				$widget->extra_data_attributes['text-if-empty'] = _("No $table found, searching for ".htmlspecialchars($query));

				$this->template->content->widgets[] = $widget->render();
			}
		}

		$this->template->inline_js = $this->inline_js;
	}

	/**
	 * This is an internal function to generate a livestatus query from a filter string.
	 *
	 * This method is public so it can be accessed from tests.
	 *
	 * @param $query Search query for string
	 * @return Livstatus query as string
	 */
	public function _queryToLSFilter($query)
	{
		$parser = new ExpParser_SearchFilter($this->object_types);
		try {
			$filter = $parser->parse( $query );
		} catch( ExpParserException $e ) {
			return false;
		}

		$query = array();

		/* Map default tables to queries */
		foreach($filter['filters'] as $table => $q ) {
			$query[$table] = array($this->andOrToQuery($q, $this->search_columns[$table]));
		}

		if( isset( $filter['filters']['_si'] ) ) {
			/* Map status information table to hosts and services */
			$query['hosts'] = array_merge(isset($query['hosts'])?$query['hosts']:array(), $query['_si']);
			$query['services'] = array_merge(isset($query['services'])?$query['services']:array(), $query['_si']);
			unset( $query['_si'] );
		} else if( isset( $filter['filters']['comments'] ) ) {
			/* Map subtables for comments (hosts and servies) */
			if( isset( $filter['filters']['services'] ) ) {
				$query['comments'][] = $this->andOrToQuery( $filter['filters']['services'],
					array_map( function($col){
						return 'service.'.$col;
					}, $this->search_columns['services'] ) );
			}
			if( isset( $filter['filters']['hosts'] ) ) {
				$query['comments'][] = $this->andOrToQuery( $filter['filters']['hosts'],
					array_map( function($col){
						return 'host.'.$col;
					}, $this->search_columns['hosts'] ) );
			}
			/* Don't search in hosts or servies if searching in comments */
			unset( $query['hosts'] );
			unset( $query['services'] );
		}
		else if( isset( $filter['filters']['services'] ) )  {
			if( isset( $filter['filters']['hosts'] ) )
				$query['services'][] = $this->andOrToQuery( $filter['filters']['hosts'],
					array_map( function($col){
						return 'host.'.$col;
					}, $this->search_columns['hosts'] ) );
			/* Don't search in hosts if searching for services, just filter on hosts... */
			unset( $query['hosts'] );
		}

		$result = array();
		foreach( $query as $table => $filters ) {
			$result[$table] = '['.$table.'] '.implode(' and ',$filters);
		}

		if( isset($filter['limit']) ) {
			$result['limit'] = intval($filter['limit']);
		}

		return $result;
	}

	private function andOrToQuery( $matches, $columns ) {
		$result = array();
		foreach( $matches as $and ) {
			$orresult = array();
			foreach( $and as $or ) {
				$or = trim($or);
				$or = str_replace('%','.*',$or);
				$or = addslashes($or);
				foreach( $columns as $col ) {
					$orresult[] = "$col ~~ \"$or\"";
				}
			}
			$result[] = '(' . implode(' or ', $orresult) . ')';
		}

		return implode(' and ',$result);
	}

	/**
	 * This is an internal function to generate a livestatus query from a filter string.
	 *
	 * This method is public so it can be accessed from tests.
	 *
	 * @param $query Search query for string
	 * @return Livstatus query as string
	 */
	public function _queryToLSFilter_MatchAll($query)
	{
		$query = str_replace('%','.*',$query);

		$filters = array();
		foreach( $this->search_columns_matchall as $table => $cols ) {
			$subfilters = array();
			foreach( $cols as $col ) {
				$subfilters[] = "$col ~~ \"".addslashes($query)."\"";
			}
			$filters[$table] = "[$table] ".implode(' or ', $subfilters);
		}

		return $filters;
	}



	/**
	 *	Handle search queries from front page search field
	 */
	public function ajax_auto_complete($q=false)
	{
		$this->_verify_access('ninja.search:read.search');

		$q = $this->input->get('query', $q);

		$this->auto_render = false;

		$result = $this->_global_search_build_filter($q);

		if( $result !== false ) {
			$obj_type = $result[0];
			$obj_name = $result[1];
			$settings = $result[2];
			$livestatus_options = $result[3];

			$ls = Livestatus::instance();
			$lsb = $ls->getBackend();

			$livestatus_options['limit'] = Kohana::config('config.autocomplete_limit');

			$data = $lsb->getTable($obj_type, $livestatus_options);
			$obj_info = array();
			$obj_data = array();

			if ($data!==false) {
				foreach ($data as $row) {
					$row = (object)$row;
					$obj_info[] = $obj_type == 'services' ? $row->{$settings['data']} . ';' . $row->{$settings['name_field']} : $row->{$settings['name_field']};
					$obj_data[] = array($settings['path'], $row->{$settings['data']});
				}
				if (!empty($obj_data) && !empty($found_str)) {
					$obj_info[] = $divider_str;
					$obj_data[] = array('', $divider_str);
					$obj_info[] = $found_str;
					$obj_data[] = array('', $found_str);
				}
			}
			$var = array('query' => $q, 'suggestions' => $obj_info, 'data' => $obj_data);

		} else {
			$var = array('query' => $q, 'suggestions' => array(), 'data' => array());
		}
		$json_str = json_encode($var);
		echo $json_str;
	}

	/**
	 * This is actually a local method for global_search to build the search query for live search.
	 *
	 * This method is public to make it testable. It doesn't interact with anything external, or take time, so it's no security issue...
	 *
	 * @param $q Search query
	 */
	public function _global_search_build_filter($q)
	{
		$parser = new ExpParser_SearchFilter($this->object_types);

		try {
			$parser->parse($q);
			$obj_type = $parser->getLastObject();
			$obj_name = $parser->getLastString();
		} catch( ExpParserException $e ) {
			$obj_type = 'hosts';
			$obj_name = $q;
		} catch( Exception $e ) {
			return false;
		}

		$obj_data = array();
		$obj_info = array();

		if ($obj_type !== false) {
			try {
				$pool = ObjectPool_Model::pool($obj_type);
				/* @var $set ObjectPool_Model */
				if($this->mayi->run($pool->all()->mayi_resource() . ':read.search')) {
					switch ($obj_type) {
						case 'hosts':         $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/listview/?q=[services] host.name="%s"'            ); break;
						case 'services':      $settings = array( 'name_field' => 'description',  'data' => 'host_name',   'path' => '/extinfo/details/?type=service&host=%s&service=%s' ); break;
						case 'hostgroups':    $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/listview/?q=[hosts] in "%s"'                      ); break;
						case 'servicegroups': $settings = array( 'name_field' => 'name',         'data' => 'name',        'path' => '/listview/?q=[services] in "%s"'                   ); break;
						case 'comments':      $settings = array( 'name_field' => 'comment_data', 'data' => 'host_name',   'path' => '/extinfo/details/?type=host&host=%s'               ); break;
						default: return false;
					}

					return array( $obj_type, $obj_name, $settings, array(
							'columns' => array_unique( array($settings['name_field'], $settings['data']) ),
							'filter' => array($settings['name_field'] => array( '~~' => str_replace('%','.*',$obj_name) ))
					) );
				}
			} catch(ORMException $e) {
				/* We should ignore it, since it's just nothing to autocomplete upon */
			}
		}
		return false;
	}

	/**
	 * Translated helptexts for this controller
	 */
	public static function _helptexts($id)
	{
		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array(
		'search_help' => sprintf(_("You may perform an AND search on hosts and services: 'h:web AND s:ping' will search for	all services called something like ping on hosts called something like web.<br /><br />
			Furthermore, it's possible to make OR searches: 'h:web OR mail' to search for hosts with web or mail in any of the searchable fields.<br /><br />
			Combine AND with OR: 'h:web OR mail AND s:ping OR http'<br /><br />
			Use si:critical to search for status information like critical<br /><br />
			Read the manual for more tips on searching.<br /><br />

			The search result is currently limited to %s rows (for each object type).<br /><br />
			To temporarily change this for your search, use limit=&lt;number&gt; (e.g limit=100) or limit=0 to disable the limit entirely."), config::get('pagination.default.items_per_page', '*')
		),
		'saved_search_help' => _('Click to save this search for later use. Your saved searches will be available by clicking on the icon just below the search field at the top of the page.'),
		'filterbox' => _('When you start to type, the visible content gets filtered immediately.<br /><br />If you press <kbd>enter</kbd> or the button "Search through all result pages", you filter all result pages but <strong>only through its primary column</strong> (<em>host name</em> for host objects, etc).')
		);
		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		}
		else
			echo sprintf(_("This helptext ('%s') is not translated yet"), $id);
	}
}
