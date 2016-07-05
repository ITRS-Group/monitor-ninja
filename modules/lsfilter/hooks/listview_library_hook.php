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
		autocomplete::add_table('hosts', 'name', "[hosts] name~~\"%s\"");
		autocomplete::add_table('saved_filters', 'filter_name', "[saved_filters] filter_name~~\"%s\"");
		autocomplete::add_table('services', 'description', "[services] description~~\"%s\"");
		autocomplete::add_table('contacts', 'name', "[contacts] name~~\"%s\"");
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

		/* Don't load js if we don't have access to those */
		if(op5MayI::instance()->run('ninja.listview:read')) {
			$controller->template->js[] = 'index.php/listview/list_commands/commands.js';
			$controller->template->js[] = 'index.php/listview/columns_config/vars';
		}

		$custom_extra_js = Module_Manifest_Model::get('lsfilter_extra_js');
		$controller->template->js = array_merge($controller->template->js, $custom_extra_js);

		$controller->template->css[] = $basepath.'views/css/LSFilterStyle.css';
		$controller->template->css[] = $basepath . 'views/form/codemirror.css';
		$controller->template->css[] = $basepath . 'views/form/show-hint.css';

	}
}

new listview_library_hook();
