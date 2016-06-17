<?php

/**
 * Hook that imports all livestatus javascript everywhere, because we need it, well, everywhere.
 */
class listview_library_hook {
	/**
	 * hook setup
	 */
	public function __construct()
	{
		Event::add('system.post_controller_constructor', array($this, 'add_files'));
	}

	/**
	 * hook callback
	 */
	public function add_files()
	{
		$controller = Event::$data;

		$js_strings = "Date.monthNames = ".json_encode(date::month_names()).";\n";
		$js_strings .= 'Date.dayNames = '.json_encode(date::day_names()).";\n";
		$controller->template->inline_js = $js_strings;

		$basepath = 'modules/lsfilter/';

		$controller->template->js[] = 'index.php/manifest/js/orm_structure.js';

		$controller->template->js[] = 'index.php/listview/renderer/table.js';
		$controller->template->js[] = 'index.php/listview/renderer/buttons.js';
		$controller->template->js[] = 'index.php/listview/renderer/extra_objects.js';
		$controller->template->js[] = 'index.php/listview/renderer/totals.js';
		$controller->template->js[] = 'index.php/listview/list_commands/commands.js';

		$controller->template->js[] = 'index.php/listview/columns_config/vars';

		$custom_extra_js = Module_Manifest_Model::get('lsfilter_extra_js');
		$controller->template->js = array_merge($controller->template->js, $custom_extra_js);

		$controller->template->css[] = $basepath.'views/css/LSFilterStyle.css';

	}
}

new listview_library_hook();
