<?php

class ListView_Controller extends Authenticated_Controller {
	public function index($default_query = "[hosts] state = 0") {
		$this->xtra_js = array();
		$basepath = 'modules/lsfilter/';
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
			foreach( $result_set->it(false,$sort) as $elem ) {
				$data[] = $elem->export();
			}


			/* TODO: fixa till stats bŠttre */
			$hostpool = new HostPool_Model();
			$servicepool = new ServicePool_Model();

			$hoststats = array(
					'host_state_up'          => $hostpool->get_by_name('std host state up'),
					'host_state_down'        => $hostpool->get_by_name('std host state down'),
					'host_state_unreachable' => $hostpool->get_by_name('std host state unreachable'),
					'host_pending'           => $hostpool->get_by_name('std host pending'),
					'host_all'               => $hostpool->get_by_name('std host all')
			);
			$servicestats = array(
					'service_state_ok'       => $servicepool->get_by_name('std service state ok'),
					'service_state_warning'  => $servicepool->get_by_name('std service state warning'),
					'service_state_critical' => $servicepool->get_by_name('std service state critical'),
					'service_state_unknown'  => $servicepool->get_by_name('std service state unknown'),
					'service_pending'        => $servicepool->get_by_name('std service pending'),
					'service_all'            => $servicepool->get_by_name('std service all')
			);

			$stats = array();
			switch( $result_set->get_table() ) {
				case 'hosts':
					$stats = $result_set->stats( $hoststats )
					       + $result_set->convert_to_object('services','host')->stats( $servicestats );
					break;
				case 'services':
					$stats = $result_set->stats( $servicestats );
					break;
			}

			$this->output_ajax( array( 'status' => 'success', 'totals' => $stats, 'data' => $data ) );
		} catch( Exception $e ) {
			$this->output_ajax( array( 'status' => 'error', 'data' => $e->getMessage() ) );
		}
	}

	private function output_ajax( $data ) {
		$this->auto_render = false;
		echo json_encode( $data );
	}
}
