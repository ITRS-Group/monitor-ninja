<?php

class ListView_Controller extends Authenticated_Controller {
	public function index() {
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
		$this->template->title = _('List view');
		$this->template->content = $lview = $this->add_view('listview/listview');
		$this->template->disable_refresh = true;
		
		$lview->query = '[hosts] name ~~ "kaka"';
	}
	
	public function fetch_ajax() {
		$query = $this->input->get('q','');
		
		$preprocessor = new LSFilterPP_Core();
		
		$parser = new LSFilter_Core($preprocessor, new LSFilterMetadataVisitor_Core());
		$metadata = $parser->parse( $query );
		
		$parser = new LSFilter_Core($preprocessor, new LSFilterSetBuilderVisitor_Core($metadata));
		$set = $parser->parse( $query );
		
		$columns = false;
		if( isset( $metadata['columns'] ) )
			$columns = $metadata['columns'];
		
		$data = array();
		foreach( $set->it($columns,array()) as $elem ) {
			$data[] = $elem->export();
		}

		$this->output_ajax( $data );
	}
	
	private function output_ajax( $data ) {
		$this->auto_render = false;
		echo json_encode( $data );
	}
}