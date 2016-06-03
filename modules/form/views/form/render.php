<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */

echo '<form class="njform-form" action="'.html::specialchars($action).'" method="GET">';
echo '<input type="hidden" name="csrf_token" value="'.Session::instance()->get(Kohana::config('csrf.csrf_token')).'"/>';
foreach($form->get_fields() as $field) {
	$form->get_view($field)->render(true);
}
echo '<div class="njform-label"></div>';
echo '<div class="njform-field">';
echo '<input type="submit" value="Send">';
echo '</div>';
echo '</form>';