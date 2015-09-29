<?php
if(!isset($manifest['table']))
	$manifest['table'] = array();
$manifest['table'][] = 'modules/lsfilter/media/js/lsfilter_renderer_table.in.js';

if(!isset($manifest['buttons']))
	$manifest['buttons'] = array();
$manifest['buttons'][] = 'modules/lsfilter/media/js/lsfilter_renderer_buttons.in.js';

if(!isset($manifest['extra_objects']))
	$manifest['extra_objects'] = array();
$manifest['extra_objects'][] = 'modules/lsfilter/media/js/lsfilter_renderer_extra_objects.in.js';
