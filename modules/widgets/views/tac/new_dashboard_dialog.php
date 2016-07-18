<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
 * This page is loaded via ajax from fancybox when selecting "new dashboard"
 */

echo form::open(url::site() . 'tac/on_new_dashboard', array('id' => 'dashboard-create-form'));
echo '<h2>New dashboard</h2>';
echo '<hr>';
echo form::input('name', '', 'required');
echo '<br />';
echo form::dropdown('layout', array(
	'3,2,1' => '3,2,1',
	'1,3,2' => '1,3,2',
));
echo '<hr>';
echo form::submit(array(), 'Yes');
echo form::input(array('type' => 'button', 'class' => 'dashboard-form-cancel'), 'Cancel');
echo form::close();