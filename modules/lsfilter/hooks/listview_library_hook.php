<?php

/**
 * Hook that imports all livestatus javascript everywhere, because we need it, well, everywhere.
 */
Event::add('system.post_controller_constructor', function() {
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

});
