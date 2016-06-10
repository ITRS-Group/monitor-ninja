<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */

echo '<form class="nj-form" action="'.html::specialchars($action).'" method="GET">';
echo '<input type="hidden" name="csrf_token" value="'.Session::instance()->get(Kohana::config('csrf.csrf_token')).'"/>';
foreach($form->get_fields() as $field) {
	$form->get_view($field)->render(true);
}
echo '<div class="nj-form-label"></div>';
echo '<div class="nj-form-field">';
echo '<input type="submit" value="Send">';
echo '</div>';
echo '</form>';
