<?php

class ListView_Controller extends Authenticated_Controller {
	public function index($default_query = "[hosts] state = 0") {
		$this->xtra_js = array();
		$basepath = 'modules/lsfilter/';
		$ormpath = 'modules/livestatusorm/';

		$this->xtra_js[] = $ormpath.'js/LivestatusStructure.js';

		$this->xtra_js[] = $basepath.'js/LSFilter.js';
		$this->xtra_js[] = $basepath.'js/LSFilterLexer.js';
		$this->xtra_js[] = $basepath.'js/LSFilterParser.js';
		$this->xtra_js[] = $basepath.'js/LSFilterPreprocessor.js';
		$this->xtra_js[] = $basepath.'js/LSFilterVisitor.js';
		$this->xtra_js[] = $basepath.'views/themes/default/js/LSFilterRenderer.js';
		$this->xtra_js[] = $basepath.'views/themes/default/js/LSFilterSearch.js';
		$this->xtra_js[] = $basepath.'views/themes/default/js/LSFilterVisualizer.js';

		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css = array();
		$this->xtra_css[] = $basepath.'views/themes/default/css/LSFilterStyle.css';
		$this->template->css_header->css = $this->xtra_css;

		$this->template->title = _('List view');
		$this->template->content = $lview = $this->add_view('listview/listview');
		$this->template->disable_refresh = true;

		$lview->query = $this->input->get('filter_query', $default_query);
	}

	public function fetch_ajax() {
		$query = $this->input->get('query','');
		$columns = $this->input->get('columns',false);
		$sort = $this->input->get('sort',array());
		$sort_asc = $this->input->get('sort_asc',true);

		/* TODO: Fix sorting better sometime
		 * Do it though ORM more orm-ly
		 * Check if columns exists and so on...
		 */
		$sort = array_map(function($el){return str_replace('.','_',$el);},$sort);
		
		if(!$sort_asc)
			$sort = array_map(function($el){return $el.' desc';},$sort);
		
		try {
			$result_set = ObjectPool_Model::get_by_query( $query );
			
			$data = array();
			foreach( $result_set->it($columns,$sort) as $elem ) {
				$data[] = $elem->export();
			}

			$this->output_ajax( array( 'status' => 'success', 'totals' => $result_set->get_totals(), 'data' => $data ) );
		} catch( Exception $e ) {
			$this->output_ajax( array( 'status' => 'error', 'data' => $e->getMessage() ) );
		}
	}

	public function fetch_saved_queries() {
		$queries = array(
					array('std host state up'          ,'[hosts] state=0 and has_been_checked=1',    'static'),
					array('std host state down'        ,'[hosts] state=1 and has_been_checked=1',    'static'),
					array('std host state unreachable' ,'[hosts] state=2 and has_been_checked=1',    'static'),
					array('std host pending'           ,'[hosts] has_been_checked=0',                'static'),
					array('std host all'               ,'[hosts] state!=999',                        'static'),
					array('std service state ok'       ,'[services] state=0 and has_been_checked=1', 'static'),
					array('std service state warning'  ,'[services] state=1 and has_been_checked=1', 'static'),
					array('std service state critical' ,'[services] state=2 and has_been_checked=1', 'static'),
					array('std service state unknown'  ,'[services] state=3 and has_been_checked=1', 'static'),
					array('std service pending'        ,'[services] has_been_checked=0',             'static'),
					array('std service all'            ,'[services] description!=""',                'static')
				);
		$mapped_queries = array_map(function($q){return array_combine(array('name','query','scope'),$q);},$queries);
		$this->output_ajax( array( 'status' => 'success', 'data' => $mapped_queries ) );
	}

	public function save_query() {
			$this->output_ajax( array( 'status' => 'success', 'data' => 'something something' ) );
	}

	private function output_ajax( $data ) {
		$this->auto_render = false;
		echo json_encode( $data );
	}
}
