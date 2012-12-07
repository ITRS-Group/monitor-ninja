<?php

class ListView_Controller extends Authenticated_Controller {
	public function index($default_query = "[hosts] state = 7") {
		
		$this->xtra_js = array();
		$basepath = 'modules/lsfilter/';
		$this->xtra_js[] = $basepath.'js/LSFilter.js';
		$this->xtra_js[] = $basepath.'js/LSFilterLexer.js';
		$this->xtra_js[] = $basepath.'js/LSFilterParser.js';
		$this->xtra_js[] = $basepath.'js/LSFilterPreprocessor.js';
		$this->xtra_js[] = $basepath.'js/LSFilterVisitor.js';
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
		$query = $this->input->get('q','');
		
		try {
			$set = ObjectPool_Model::get_by_query( $query );
			
			$columns = false;
			if( isset( $metadata['columns'] ) )
				$columns = $metadata['columns'];
			
			$data = array();
			foreach( $set->it($columns,array()) as $elem ) {
				$data[] = $elem->export();
			}
	
			$this->output_ajax( array( 'status' => 'success', 'data' => $data ) );
		} catch( Exception $e ) {
			$this->output_ajax( array( 'status' => 'error', 'data' => $e->getMessage() ) );
		}
	}
	
	private function output_ajax( $data ) {
		$this->auto_render = false;
		echo json_encode( $data );
	}
}