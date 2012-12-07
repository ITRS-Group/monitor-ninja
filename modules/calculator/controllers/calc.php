<?php

class Calc_Controller extends Authenticated_Controller {
	public function index() {
		$this->xtra_js = array();
		$basepath = 'modules/calculator/';
		$this->xtra_js[] = $basepath.'js/Calculator.js';
		$this->xtra_js[] = $basepath.'js/CalculatorLexer.js';
		$this->xtra_js[] = $basepath.'js/CalculatorParser.js';
		$this->xtra_js[] = $basepath.'js/CalculatorPreprocessor.js';
		$this->xtra_js[] = $basepath.'js/CalculatorVisitor.js';
		$this->xtra_js[] = $basepath.'views/themes/default/js/CalculatorVisualizer.js';

		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;
		$this->template->title = _('Calculator');
		$this->template->content = $lview = $this->add_view('calc/calc');
		$this->template->disable_refresh = true;
		
		$lview->query = '';
	}
}