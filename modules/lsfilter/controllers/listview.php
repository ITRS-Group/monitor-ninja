<?php

/**
 * List view-related calls
 *
 * Display the listview container, load scripts, and handles ajax-requests
 */
class ListView_Controller extends Authenticated_Controller {

	/**
	 * Display a listview with a given query, entrypoint for listview
	 */
	public function index($q = "[hosts] all") {
		$this->template->listview_refresh = true;
		$query = $this->input->get('q', $q);
		$query_order = $this->input->get('s', '');

		$basepath = 'modules/lsfilter/';

		$this->xtra_js[] = 'index.php/manifest/js/orm_structure.js';

		$this->xtra_js[] = $basepath.'js/LSFilter.js';
		$this->xtra_js[] = $basepath.'js/LSFilterLexer.js';
		$this->xtra_js[] = $basepath.'js/LSFilterParser.js';
		$this->xtra_js[] = $basepath.'js/LSFilterPreprocessor.js';
		$this->xtra_js[] = $basepath.'js/LSFilterVisitor.js';

		$this->xtra_js[] = $basepath.'js/LSColumns.js';
		$this->xtra_js[] = $basepath.'js/LSColumnsLexer.js';
		$this->xtra_js[] = $basepath.'js/LSColumnsParser.js';
		$this->xtra_js[] = $basepath.'js/LSColumnsPreprocessor.js';
		$this->xtra_js[] = $basepath.'js/LSColumnsVisitor.js';

/*		$this->xtra_js[] = $basepath.'media/js/lib.js'; saved searched loaded globally */
		$this->xtra_js[] = $basepath.'media/js/LSFilterRenderer.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterVisitors.js';
		$this->xtra_js[] = 'index.php/listview/renderer/table.js';

		$this->xtra_js[] = $basepath.'media/js/LSFilterMain.js';

		$this->xtra_js[] = $basepath.'media/js/LSFilterHistory.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterList.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterListEvents.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterListTableDesc.js';
/*		$this->xtra_js[] = $basepath.'media/js/LSFilterSaved.js'; saved searched loaded globally */
		$this->xtra_js[] = $basepath.'media/js/LSFilterTextarea.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterVisual.js';

		$this->xtra_js[] = $basepath.'media/js/LSFilterMultiselect.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterInputWindow.js';

		$this->xtra_js[] = 'index.php/listview/columns_config/vars';

		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css = array();
		$this->xtra_css[] = $basepath.'views/css/LSFilterStyle.css';
		$this->template->css_header->css = $this->xtra_css;

		$this->template->title = _('List view');
		$this->template->toolbar = new Toolbar_Controller( $this->template->title );

		$this->template->content = $lview = $this->add_view('listview/listview');
		$this->template->disable_refresh = true;

		// add context menu items (hidden in html body)
		$this->template->context_menu = $this->add_view('status/context_menu');

		$this->template->toolbar->button( '<span class="icon-16 x16-check-boxes"></span>', array(
			"title" => "Send multi action",
			"id" => "show-filter-query-multi-action"
		) );

		$this->template->toolbar->button( '<span class="icon-16 x16-filter"></span>', array(
			"title" => "Show/Edit Text Filter",
			"id" => "show-filter-query-builder-button"
		) );

		$this->template->toolbar->info( '<div id="filter_result_totals"></div>' );

		$lview->query = $query;
		$lview->query_order = $query_order;
	}

	/**
	 * Fetches the users columns configuration, as a javascript.
	 */
	public function columns_config($tmp = false) {

		/* Fetch all column configs for user */
		$columns = array();
		$columns_default = Kohana::config('listview.default.columns');
		foreach( $columns_default as $table => $default ) {
			/* Build a list of order to expand columns, per table
			 * The result of the previous line will be handled as the "default" keyword in the next one
			 */
			$columns[$table] = array(
					$default,
					config::get('listview.columns.'.$table, '*')
					);
		}



		/* This shouldn't have a standard template */
		$this->template = $lview = $this->add_view('listview/js');
		$this->template->vars = array(
			'lsfilter_list_columns' => $columns
			);

		/* Render and die... cant print anything like profiler output here */
		$this->template->render(true);
		exit();
	}

	/**
	 * Executes a search in the orm structure for a given query.
	 */
	public function fetch_ajax() {
		$query = $this->input->get('query','');
		$columns = $this->input->get('columns',false);
		$sort = $this->input->get('sort',array());

		$limit = $this->input->get('limit',false);
		$offset = $this->input->get('offset',false);

		if( $limit === false ) {
			return json::fail( array( 'data' => _("No limit specified")) );
		}

		/* TODO: Fix sorting better sometime
		 * Do it though ORM more orm-ly
		 * Check if columns exists and so on...
		 */
		$sort = array_map(function($el){return str_replace('.','_',$el);},$sort);

		try {
			$result_set = ObjectPool_Model::get_by_query( $query );

			/*
			 * Some magic column filtering
			 *
			 * We need to strip away requested columns that isn't available.
			 * The ORM layer isn't, and shouldn't, be forgiving about columns
			 * taht doesn't exist. But due to custom columns, that (yet) doesn't
			 * know about "virtual" columns (columns defined as methods in the
			 * ORM models), we need to expect that those exist, try to request
			 * them, and then handle them as undefined if not defined in the
			 * result, instead of handling them as undefined already in the
			 * column definition.
			 */
			$structure = Module_Manifest_Model::get('orm_structure');

			/* Extract virtual columns, so we can filter against structure */
			$raw_columns = $result_set->validate_columns($columns);

			/* Check each column against structure */
			$columns = array();
			foreach($raw_columns as $column) {
				$parts = explode('.',$column);
				$table = $result_set->get_table();
				$accept = true;
				/* Columns can be object.field, so iterate over each part */
				foreach($parts as $part) {
					if( isset($structure[$table][$part]) ) {
						if($structure[$table][$part][0] == 'object') {
							$table = $structure[$table][$part][1];
						}
					} else {
						$accept = false;
					}
				}
				/* Write back columns we accept */
				if( $accept ) {
					$columns[] = $column;
				}
			}

			$data = array();
			foreach( $result_set->it($columns,$sort,$limit,$offset) as $elem ) {
				$data[] = $elem->export();
			}

			return json::ok( array(
				'totals' => $result_set->get_totals(),
				'data' => $data,
				'table' => $result_set->get_table(),
				'count' => count($result_set)
			) );
		} catch( LSFilterException $e ) {
			return json::fail( array(
				'data' => $e->getMessage().' at "'.substr($e->get_query(), $e->get_position()).'"',
				'query' => $e->get_query(),
				'position' => $e->get_position()
				));
		} catch( ORMException $e ) {
			return json::fail( array(
				'data' => $e->getMessage()
				));
		} catch( Exception $e ) {
			$this->log->log('error', $e->getMessage() . ' at ' . $e->getFile() . '@' . $e->getLine());

			return json::fail( array(
				'data' => $e->getMessage().' at '.$e->getFile().'@'.$e->getLine()
				));
		}
	}

	/**
	 * Fetch a list of the saved queries for use with ajax
	 */
	public function fetch_saved_filters() {
		$queries = LSFilter_Saved_Queries_Model::get_queries();
		return json::ok( array( 'status' => 'success', 'data' => $queries ) );
	}

	/**
	 * Save a named query
	 */
	public function save_filter() {
		$name = $this->input->get('name',false);
		$query = $this->input->get('query','');
		$scope = $this->input->get('scope','user');

		try {

			$result = LSFilter_Saved_Queries_Model::save_query($name, $query, $scope);

			if( $result !== false )
				return json::ok( array('status'=>'error', 'data' => $result) );


			return json::ok( array( 'status' => 'success', 'data' => 'success' ) );
		}
		catch( Exception $e ) {
			return json::ok( array( 'status' => 'error', 'data' => $e->getMessage() ) );
		}
	}

	/**
	 * Save a named query
	 */
	public function delete_saved_filter() {
		$id = $this->input->get('id',false);

		try {

			$result = LSFilter_Saved_Queries_Model::delete_query($id);

			if( $result !== false )
				return json::ok( array('status'=>'error', 'data' => $result) );

			return json::ok( array( 'status' => 'success', 'data' => 'success' ) );
		}
		catch( Exception $e ) {
			return json::ok( array( 'status' => 'error', 'data' => $e->getMessage() ) );
		}
	}


	/**
	 * Return a manifest variable as a javascript file, for loading through a script tag
	 */
	public function renderer( $name = false ) {
		if( substr( $name, -3 ) == '.js' ) {
			$name = substr( $name, 0, -3 );
		}

		$this->auto_render = false;
		$renderers_files = Module_Manifest_Model::get( 'lsfilter_renderers' );

		header('Content-Type: text/javascript');

		print "var listview_renderer_".$name." = {};\n\n";

		$files = array();
		if( isset( $renderers_files[$name] ) ) {
			$files = $renderers_files[$name];
		}
		sort($files);

		foreach( $files as $renderer ) {
			print "\n/".str_repeat('*',79)."\n";
			print " * Output file: ".$renderer."\n";
			print " ".str_repeat('*',78)."/\n";
			if( is_readable(DOCROOT.$renderer) ) {
				readfile(DOCROOT.$renderer);
			} else {
				print "// ERROR: Can't open file...\n\n";
			}
		}
	}

	/**
	 * Translated helptexts for this controller
	 */
	public static function _helptexts($id)
	{

		$parts = explode('.',$id);
		if( count($parts) == 3 && $parts[0] == 'listview' && $parts[1] == 'columns' ) {
			printf(_("A comma-seperated list of columns visible in the list view for table %s. Use string \"all\" to see all columns. See documentation for advanced syntax and column names."), $parts[2]);
			return;
		}

		# Tag unfinished helptexts with @@@HELPTEXT:<key> to make it
		# easier to find those later
		$helptexts = array();

		if (array_key_exists($id, $helptexts)) {
			echo $helptexts[$id];
		}
		else
			printf(_("This helptext ('%s') is not translated yet"), $id);
	}
}
