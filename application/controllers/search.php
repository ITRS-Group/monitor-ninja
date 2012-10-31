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
				'hosts' => array( 'name' ),
				'services' => array( 'description', 'display_name' ),
				'hostgroups' => array( 'name', 'alias' ),
				'servicegroups' => array( 'name', 'alias' ),
				'comments' => array( 'author', 'comment' )
				);

	/**
	*	Provide search functionality for all object types
	*/
	public function lookup($query=false, $obj_type=false)
	{
		/* FIXME: implement these, maybe...
		$obj_type = $this->input->get('obj_type', $obj_type);

		$host = trim($this->input->get('host', false));
		$hostgroup = trim($this->input->get('hostgroup', false));
		$service = trim($this->input->get('service', false));
		$servicegroup = trim($this->input->get('servicegroup', false));
		$comment = trim($this->input->get('comment', false));
		$status = trim($this->input->get('status', false));
		*/
		$query = trim($this->input->get('query', $query));
		
		$this->template->content = $this->add_view('search/result');

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css[] = $this->add_path('css/default/jquery-ui-custom.css');
		$this->template->css_header->css = $this->xtra_css;

		$content = $this->template->content;
		$content->date_format_str = nagstat::date_format();

		$ls = Livestatus::instance();
		
		$filters = $this->queryToLSFilter( $query );
		if( $filters === false ) /* If no advanced query found, fall through */
			$filters = $this->queryToLSFilter_MatchAll( $query );
		
		$match = false;
		$limit = isset($filters['limit']) ? $filters['limit'] : false;
		
		
		try {
			if( isset( $filters['hosts'] ) ) {
				$res = $ls->getHosts(array(
						'extra_header'=>$filters['hosts'],
						'limit'=>$limit
						));
				if( count($res) ) {
					$content->host_result = $res;
					$match = true;
				}
			}
		} catch( LivestatusException $e ) {}
		
		
		try {
			if( isset( $filters['services'] ) ) {
				$res = $ls->getServices(array(
						'extra_header'=>$filters['services'],
						'limit'=>$limit
						));
				if( count($res) ) {
					$content->service_result = $res;
					$match = true;
				}
			}
		} catch( LivestatusException $e ) {}
		
		
		try {
			if( isset( $filters['hostgroups'] ) ) {
				$res = $ls->getHostgroups(array(
						'extra_header'=>$filters['hostgroups'],
						'limit'=>$limit
						));
				if( count($res) ) {
					$content->hostgroup_result = $res;
					$match = true;
				}
			}
		} catch( LivestatusException $e ) {}
		
		
		try {
			if( isset( $filters['servicegroups'] ) ) {
				$res = $ls->getServicegroups(array(
						'extra_header'=>$filters['servicegroups'],
						'limit'=>$limit
						));
				if( count($res) ) {
					$content->servicegroup_result = $res;
					$match = true;
				}
			}
		} catch( LivestatusException $e ) {}
		
		
		try {
			if( isset( $filters['comments'] ) ) {
				$res = $ls->getComments(array(
						'extra_header'=>$filters['comments'],
						'limit'=>$limit
						));
				if( count($res) ) {
					$content->comment_result = $res;
					$match = true;
				}
			}
		} catch( LivestatusException $e ) {}
		
		
		if( !$match ) {
			$content->no_data = _("Nothing found");
		}
		
		
		$content->limit_str = false;
		$content->query = $query;
		$content->show_display_name = true;
		$content->show_notes = true;
		
		/**
		 * Modify config/config.php to enable NACOMA
		 * and set the correct path in config/config.php,
		 * if installed, to use this
		 */
		if (nacoma::link()!==false) {

			$label_nacoma = _('Configure this object using NACOMA (Nagios Configuration Manager)');
			$content->nacoma_link = 'configuration/configure/';
		
		}
	}
	
	private function queryToLSFilter($query)
	{
		$parser = new ExpParser_SearchFilter();
		try {
			$filter = $parser->parse( $query );
		} catch( ExpParserException $e ) {
			return false;
		}
		
		$lsfilter = array();
		
		if( isset( $filter['filters']['services'] ) )  {
			$lsfilter['services'] = $this->andOrToLivestatus( $filter['filters']['services'], $this->search_columns['services'] );
			if( isset( $filter['filters']['hosts'] ) )
				$lsfilter['services'] .= $this->andOrToLivestatus( $filter['filters']['hosts'],
						array_map( function($col){return 'host_'.$col;}, $this->search_columns['hosts'] ) );
		}
		else if( isset( $filter['filters']['hosts'] ) ) {
			$lsfilter['hosts'] = $this->andOrToLivestatus( $filter['filters']['hosts'], $this->search_columns['hosts'] );
		}
		
		
		if( isset( $filter['filters']['hostgroups'] ) ) {
			$lsfilter['hostgroups'] = $this->andOrToLivestatus( $filter['filters']['hostgroups'], $this->search_columns['hostgroups'] );
		}
		
		if( isset( $filter['filters']['servicegroups'] ) ) {
			$lsfilter['servicegroups'] = $this->andOrToLivestatus( $filter['filters']['servicegroups'], $this->search_columns['servicegroups'] );
		}
		
		
		
		if( isset( $filter['limit'] ) ) {
			$lsfilter['limit'] = $filter['limit'];
		}
		
		return $lsfilter;
	}
	
	private function andOrToLivestatus( $matches, $columns ) {
		$result = '';
		foreach( $matches as $and ) {
			$orcount = 0;
			foreach( $and as $or ) {
				$or = trim($or);
				foreach( $columns as $col ) {
					$result .= "Filter: $col ~~ .*$or.*\n";
					$orcount++;
				}
			}
			if( $orcount > 1)
				$result .= "Or: ".$orcount."\n";
		}
		/* Implicit and; don't add "And: $andcount\n" to $result */
		
		return $result;
	}
	
	private function queryToLSFilter_MatchAll($query)
	{
		
		$filters = array();
		foreach( $this->search_columns as $table => $cols ) {
			$filters[$table] = "";
			foreach( $cols as $col ) {
				$filters[$table] .= "Filter: $col ~~ .*$query.*\n";
			}
			if( count( $cols ) > 1 ) {
				$filters[$table] .= "Or: ".count( $cols )."\n";
			}
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
