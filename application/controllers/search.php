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

	protected $object_types = array(
		'h'  => 'hosts',
		's'  => 'services',
		'c'  => 'comments',
		'hg' => 'hostgroups',
		'sg' => 'servicegroups',
		'si' => '_si'
	);

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

		$parser = new ExpParser_SearchFilter($this->object_types);

		try {
			/* Is the query a oldschool search filter? h:kaka or boll */
			$filters = $parser->parse($query);
			$translator = new ExpParser_Translator($this->search_columns);
			$ls_filters = $translator->translate($filters);

		} catch(ExpParserException $e) {
			/* Fallback on match everything */
			$query = str_replace('%','.*',$query);
			$translator = new ExpParser_Translator($this->search_columns_matchall);

			$ls_filters = $translator->translate(array(
				"global" => true,
				"filters" => array_reduce(
					array_keys($this->search_columns_matchall),
					function ($filters, $table) use (&$query) {
						$filters[$table] = array(array($query));
						return $filters;
					}, array()
				)
			));
		}

		if(count($ls_filters)==1) {
			return url::redirect('listview?'.http_build_query(array('q'=>reset($ls_filters))));
		}

		$limit = false;
		if(isset($filters['limit'])) {
			$limit = $filters['limit'];
			unset($filters['limit']);
		}

		$this->render_queries($ls_filters, $original_query, $limit);
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

		$username = Auth::instance()->get_user()->get_username();

		if( $limit === false ) {
			$limit = config::get('pagination.default.items_per_page');
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
	 *	Handle search queries from front page search field
	 */
	public function ajax_auto_complete($q = false)
	{
		$this->auto_render = false;
		$this->_verify_access('ninja.search:read.search');

		$q = $this->input->get('query', $q);

		$parser = new ExpParser_SearchFilter($this->object_types);
		$limit = Kohana::config('config.autocomplete_limit');
		$result = array();

		try {
			$parser->parse($q);
			$obj_type = $parser->getLastObject();
			$obj_name = $parser->getLastString();
		} catch( ExpParserException $e ) {
			$obj_type = 'hosts';
			$obj_name = $q;
		} catch(Exception $e) {
			/* Run through to obj_type false */
		}

		if ($obj_type !== false) {
			try {

				$pool = ObjectPool_Model::pool($obj_type);
				$linkprovider = LinkProvider::factory();
				$path_generator = null;
				$columns = array();

				if($this->mayi->run($pool->all()->mayi_resource() . ':read.search')) {
					switch ($obj_type) {
					case 'hosts':
						$columns = array('name');
						$set = $pool->all()->reduce_by('name', $obj_name, '~~');
						$path_generator = function ($object) use ($linkprovider) {
							return $linkprovider->get_url('listview', null, array(
								'q' => sprintf('[services] host.name="%s"', $object->get_name())
							));
						};
						break;
					case 'services':
						$columns = array('host.name', 'description');
						$set = $pool->all()->reduce_by('description', $obj_name, '~~');
						$path_generator = function ($object) use ($linkprovider) {
							return $linkprovider->get_url('extinfo', 'details', array(
								'type' => 'service',
								'host' => $object->get_host()->get_name(),
								'service' => $object->get_description()
							));
						};
						break;
					case 'hostgroups':
						$columns = array('name');
						$set = $pool->all()->reduce_by('name', $obj_name, '~~');
						$path_generator = function ($object) use ($linkprovider) {
							return $linkprovider->get_url('listview', null, array(
								'q' => sprintf('[hosts] in "%s"', $object->get_name())
							));
						};
						break;
					case 'servicegroups':
						$columns = array('name');
						$set = $pool->all()->reduce_by('name', $obj_name, '~~');
						$path_generator = function ($object) use ($linkprovider) {
							return $linkprovider->get_url('listview', null, array(
								'q' => sprintf('[services] in "%s"', $object->get_name())
							));
						};
						break;
					case 'comments':
						$columns = array('host.name', 'name');
						$set = $pool->all()->reduce_by('name', $obj_name, '~~');
						$path_generator = function ($object) use ($linkprovider) {
							return $linkprovider->get_url('extinfo', 'details', array(
								'type' => 'host',
								'host' => $object->get_name()
							));
						};
						break;
					default:
						$set = $pool->none();
					}

					foreach ($set->it($columns, array(), $limit) as $object) {
						$result[$object->get_key()] = $path_generator($object);
					}
				}
			} catch(ORMException $e) {
				/* We should ignore it, since it's just nothing to autocomplete upon */
			}
		}

		json::ok(array(
			'query' => $q,
			'suggestions' => array_keys($result),
			'data' => array_values($result)
		));

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
			To temporarily change this for your search, use limit=&lt;number&gt; (e.g limit=100) or limit=0 to disable the limit entirely."), config::get('pagination.default.items_per_page')
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
