<?php

/**
 * List view-related calls
 *
 * Display the listview container, load scripts, and handles ajax-requests
 */
class ListView_Controller extends Ninja_Controller {

	/**
	 * Display a listview with a given query, entrypoint for listview
	 */
	public function index($q = "[hosts] all") {
		$this->_verify_access('ninja.listview:view');

		$this->template->listview_refresh = true;
		$query = $this->input->get('q', $q);
		$query_order = $this->input->get('s', '');


		$basepath = 'modules/lsfilter/';

		$this->template->js[] = $basepath.'media/js/LSFilterMain.js';
		$this->template->js[] = $basepath.'media/js/LSFilterHistory.js';
		$this->template->js[] = $basepath.'media/js/LSFilterTextarea.js';
		$this->template->js[] = $basepath.'media/js/LSFilterVisual.js';
		$this->template->js[] = $basepath.'media/js/LSFilterMultiselect.js';
		$this->template->js[] = $basepath.'media/js/LSFilterInputWindow.js';

		$this->template->title = _('List view');
		$this->template->toolbar = new Toolbar_Controller( $this->template->title );
		$this->template->content = $lview = $this->add_view('listview/listview');
		$this->template->disable_refresh = true;

		// add context menu items (hidden in html body)
		$this->template->context_menu = $this->add_view('status/context_menu');

		$this->template->toolbar->should_render_buttons(true);
		$this->template->toolbar->info('<div id="filter_result_totals"></div>');
		$this->template->js_strings .= "var lsfilter_query = ".json_encode($query).";\n";
		$this->template->js_strings .= "var lsfilter_query_order = ".json_encode($query_order).";\n";
		$this->template->js_strings .= "var lsfilter_per_page = ".intval(config::get('pagination.default.items_per_page','*')).";\n";
	}

	/**
	 * Fetches the users columns configuration, as a javascript.
	 */
	public function columns_config($tmp = false) {
		$this->_verify_access('ninja.listview:view');

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
		$this->_verify_access('ninja.listview:view');

		$query = $this->input->get('query','');
		$columns = $this->input->get('columns',false);
		$sort = $this->input->get('sort',array());

		$limit = $this->input->get('limit',false);
		$offset = $this->input->get('offset',false);

		if( $limit === false ) {
			return json::fail( array( 'data' => _("No limit specified")) );
		}

		try {
			$result_set = ObjectPool_Model::get_by_query( $query );
			/* @var $result_set ObjectSet_Model */

			$messages = array();
			$perfdata = array();
			if($this->mayi->run($result_set->mayi_resource().":view.list", array(), $messages, $perfdata)) {
				$data = array();
				foreach( $result_set->it($columns,$sort,$limit,$offset) as $elem ) {
					$obj = $elem->export();
					$obj['_table'] = $elem->get_table();
					$data[] = $obj;
				}

				return json::ok( array(
					'totals' => $result_set->get_totals(),
					'data' => $data,
					'table' => $result_set->get_table(),
					'count' => count($result_set)
				) );
			} else {
				return json::fail( array(
					'data' => "You don't have permission to show ".$result_set->get_table(),
					'query' => $query,
					'messages' => $messages
				), 403);
			}
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
		$this->_verify_access('ninja.listview:view');

		$queries = LSFilter_Saved_Queries_Model::get_queries();
		return json::ok( array( 'status' => 'success', 'data' => $queries ) );
	}

	/**
	 * Save a named query
	 */
	public function save_filter() {
		$this->_verify_access('ninja.listview:view');

		$name = $this->input->get('name',false);
		$query = $this->input->get('query','');
		$scope = $this->input->get('scope','user');

		try {
			LSFilter_Saved_Queries_Model::save_query($name, $query, $scope);
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
		$this->_verify_access('ninja.listview:view');

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
		$this->_verify_access('ninja.listview:view');

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
