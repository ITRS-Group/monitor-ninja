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
	/**
	 * Contains a list of columns to search in, depending on table.
	 *
	 * @var array of arrays.
	 */
	protected $search_columns = array(
		'hosts' => array( 'name' , 'address', 'plugin_output' ),
		'services' => array( 'description', 'display_name', 'plugin_output' ),
		'hostgroups' => array( 'name', 'alias' ),
		'servicegroups' => array( 'name', 'alias' ),
		'comments' => array( 'author', 'comment' )
	);

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
		$query = trim($this->input->get('query', $query));

		if(preg_match('/^\[[a-zA-Z]+\]/', $query)) {
			return url::redirect('listview?'.http_build_query(array('q'=>$query)));
		}
		$filters = $this->queryToLSFilter( $query );
		if($filters === false) {
			$filters = $this->queryToLSFilter_MatchAll( $query );
		}

		if(count($filters)==1) {
			return url::redirect('listview?'.http_build_query(array('q'=>reset($filters))));
		}
		
		$limit = false;
		if(isset($filters['limit'])) {
			$limit = $filters['limit'];
			unset($filters['limit']);
		}

		$this->render_queries( $filters, $limit );
	}

	/**
	 * Render a list of queries as a page containing listview widgets
	 *
	 * @param $queries list of queries
	 */
	private function render_queries( $queries, $limit=false ) {
		if( !is_array($queries) ) {
			$queries = array($queries);
		}

		$this->template->content         = $this->add_view('search/result');
		$this->template->css_header      = $this->add_view('css_header');
		$this->template->js_header       = $this->add_view('js_header');

		$content = $this->template->content;
		$content->date_format_str = nagstat::date_format();

		$this->xtra_js = array();
		$this->xtra_css = array();
		$this->template->content->widgets = array();
		
		$this->xtra_js[] = $this->add_path('/js/widgets.js');

		$username = Auth::instance()->get_user()->username;
		
		foreach( $queries as $table => $query ) {
			$setting = array('query'=>$query);
			if($limit !== false) {
				$setting['limit'] = $limit;
			}
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
				
			$widget->set_fixed($query);
				
			$this->template->content->widgets[] = $widget->render();
		}

		$this->template->js_header->js = $this->xtra_js;
		$this->template->css_header->css = $this->xtra_css;
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
	public function queryToLSFilter($query)
	{
		$parser = new ExpParser_SearchFilter();
		try {
			$filter = $parser->parse( $query );
		} catch( ExpParserException $e ) {
			return false;
		}

		$table = false;
		$query = array();

		if( isset( $filter['filters']['comments'] ) ) {
			$table = 'comments';
			if( isset( $filter['filters']['services'] ) ) {
				$query[] = $this->andOrToQuery( $filter['filters']['services'],
					array_map( function($col){
						return 'service.'.$col;
					}, $this->search_columns['services'] ) );
			}
			if( isset( $filter['filters']['hosts'] ) ) {
				$query[] = $this->andOrToQuery( $filter['filters']['hosts'],
					array_map( function($col){
						return 'host.'.$col;
					}, $this->search_columns['hosts'] ) );
			}
		}
		else if( isset( $filter['filters']['services'] ) )  {
			$table = 'services';
			if( isset( $filter['filters']['hosts'] ) )
				$query[] = $this->andOrToQuery( $filter['filters']['hosts'],
					array_map( function($col){
						return 'host.'.$col;
					}, $this->search_columns['hosts'] ) );
		}
		else if( isset( $filter['filters']['hosts'] ) ) {
			$table = 'hosts';
		}
		else if( isset( $filter['filters']['hostgroups'] ) ) {
			$table = 'hostgroups';
		}
		else if( isset( $filter['filters']['servicegroups'] ) ) {
			$table = 'servicegroups';
		}

		$query[] = $this->andOrToQuery( $filter['filters'][$table], $this->search_columns[$table] );

		if( $table === false )
			return false;
		
		$result = array($table => '['.$table.'] '.implode(' and ',$query));
		
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
	public function queryToLSFilter_MatchAll($query)
	{

		$query = str_replace('%','.*',$query);

		$filters = array();
		foreach( $this->search_columns as $table => $cols ) {
			$subfilters = array();
			foreach( $cols as $col ) {
				$subfilters[] = "$col ~~ \"$query\"";
			}
			$filters[$table] = "[$table] ".implode(' or ', $subfilters);
		}

		return $filters;
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
