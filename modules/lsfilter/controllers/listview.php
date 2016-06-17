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
		$this->_verify_access('ninja.listview:read');

		$this->template->listview_refresh = true;
		$query = $this->input->get('q', $q);
		$query_order = $this->input->get('s', '');

		$this->template->title = _('List view');
		$this->template->toolbar = new Toolbar_Controller( $this->template->title );
		$this->template->content = $lview = $this->add_view('listview/listview');
		$this->template->disable_refresh = true;

		$this->template->toolbar->should_render_buttons(true);
		$this->template->toolbar->info('<div id="filter_result_totals"></div>');

		$json_query = json_encode($query);
		$json_query_order = json_encode($query_order);
		$per_page = intval(config::get('pagination.default.items_per_page'));

		$this->template->js_strings .= <<<EOF
var lsfilter_query = $json_query;
var lsfilter_query_order = $json_query_order;
var lsfilter_per_page = $per_page;
$().ready(function() {
	lsfilter_main.init();
	lsfilter_main.update(lsfilter_query, false, lsfilter_query_order);
});
EOF;
	}

	/**
	 * Fetches the users columns configuration, as a javascript.
	 */
	public function columns_config($tmp = false) {
		$this->_verify_access('ninja.listview:read');

		/* Fetch all column configs for user */
		$columns = array();
		$columns_default = Kohana::config('listview.default.columns');
		foreach( $columns_default as $table => $default ) {
			/* Build a list of order to expand columns, per table
			 * The result of the previous line will be handled as the "default" keyword in the next one
			 */
			$columns[$table] = array(
				$default,
				config::get('listview.columns.'.$table)
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
		$this->_verify_access('ninja.listview:read');

		$query = $this->input->get('query',$this->input->post('query',''));
		$columns = $this->input->get('columns',$this->input->post('columns',false));
		$sort = $this->input->get('sort',$this->input->post('sort',array()));

		$limit = $this->input->get('limit',$this->input->post('limit',false));
		$offset = $this->input->get('offset',$this->input->post('offset',false));

		if( $limit === false ) {
			return json::fail(array('data' => _("No limit specified")), 400);
		}

		try {
			$result_set = ObjectPool_Model::get_by_query( $query );
			/* @var $result_set ObjectSet_Model */

			$messages = array();
			$perfdata = array();
			if($this->mayi->run($result_set->mayi_resource().":read.list", array(), $resource_messages, $perfdata)) {

				/*
				 * Messages should be both global notifications and messages
				 * from the resouce
				 */
				$messages = array();

				foreach($resource_messages as $message) {
					$this->notices[] = new InformationNotice_Model($message);
				}

				foreach ($this->notices as $notice) {
					$messages[] = array(
						"message" => $notice->get_message(),
						"type" => $notice->get_typename()
					);
				}

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
					'count' => count($result_set),
					'messages' => $messages
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
		} catch (ORMException $e) {
			return json::fail(array('data' => $e->getMessage()));
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
		$this->_verify_access('ninja.listview:read');

		$queries = LSFilter_Saved_Queries_Model::get_queries();
		return json::ok( array( 'status' => 'success', 'data' => $queries ) );
	}

	/**
	 * Save a named query
	 */
	public function save_filter() {
		$this->_verify_access('ninja.listview:read');

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
		$this->_verify_access('ninja.listview:read');

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
		$this->_verify_access('ninja.listview:read');

		if( substr( $name, -3 ) == '.js' ) {
			$name = substr( $name, 0, -3 );
		}

		$this->auto_render = false;
		$renderers_files = Module_Manifest_Model::get( 'lsfilter_renderers' );

		header('Content-Type: text/javascript');

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
	 * Return a manifest variable as a javascript file, for loading through a script tag
	 */
	public function list_commands( $name = false ) {
		$this->_verify_access('ninja.listview:read');

		$type = 'json';

		if( substr( $name, -3 ) == '.js' ) {
			$name = substr( $name, 0, -3 );
			$type = 'js';
		}


		$this->auto_render = false;
		$tables = ObjectPool_Model::load_table_classes();

		$commands = array();

		foreach( $tables as $table => $classes ) {
			$obj_class = $classes['object'];
			$commands[$table] = $obj_class::list_commands_static();
		}

		switch($type) {
			case 'js':
				header('Content-Type: text/javascript');
				printf("var listview_commands = %s;\n\n", json_encode($commands));
				break;
			case 'json':
				header('Content-Type: application/json');
				echo json_encode($commands);
				break;
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
