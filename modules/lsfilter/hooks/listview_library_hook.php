<?php

class listview_library_hook {
	public function __construct()
	{
		Event::add('system.post_controller_constructor', array($this, 'add_files'));
	}

	public function add_files()
	{
		$controller = Event::$data;

		$js_strings = "Date.monthNames = ".json_encode(date::month_names()).";\n";
		$js_strings .= 'Date.dayNames = '.json_encode(date::day_names()).";\n";
		$controller->template->inline_js = $js_strings;

		$basepath = 'modules/lsfilter/';


		$controller->template->js[] = 'index.php/manifest/js/orm_structure.js';

		$controller->template->js[] = $basepath.'js/LSFilter.js';
		$controller->template->js[] = $basepath.'js/LSFilterLexer.js';
		$controller->template->js[] = $basepath.'js/LSFilterParser.js';
		$controller->template->js[] = $basepath.'js/LSFilterPreprocessor.js';
		$controller->template->js[] = $basepath.'js/LSFilterVisitor.js';

		$controller->template->js[] = $basepath.'js/LSColumns.js';
		$controller->template->js[] = $basepath.'js/LSColumnsLexer.js';
		$controller->template->js[] = $basepath.'js/LSColumnsParser.js';
		$controller->template->js[] = $basepath.'js/LSColumnsPreprocessor.js';
		$controller->template->js[] = $basepath.'js/LSColumnsVisitor.js';

		/*		$controller->template->js[] = $basepath.'media/js/lib.js'; saved searched loaded globally */
		$controller->template->js[] = $basepath.'media/js/LSFilterVisitors.js';
		$controller->template->js[] = 'index.php/listview/renderer/table.js';
		$controller->template->js[] = 'index.php/listview/renderer/buttons.js';
		$controller->template->js[] = 'index.php/listview/renderer/extra_objects.js';
		$controller->template->js[] = 'index.php/listview/renderer/totals.js';

		$controller->template->js[] = $basepath.'media/js/LSFilterList.js';
		$controller->template->js[] = $basepath.'media/js/LSFilterListEvents.js';
		$controller->template->js[] = $basepath.'media/js/LSFilterListTableDesc.js';


		$controller->template->js[] = 'index.php/listview/columns_config/vars';

		$custom_extra_js = Module_Manifest_Model::get('lsfilter_extra_js');
		$controller->template->js = array_merge($controller->template->js, $custom_extra_js);

		$controller->template->css[] = $basepath.'views/css/LSFilterStyle.css';
	}
}

new listview_library_hook();
