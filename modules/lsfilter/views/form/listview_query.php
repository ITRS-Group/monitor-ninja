<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");

echo '<div class="nj-form-field nj-form-field-listview-query">';
echo '<label>';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<textarea data-table="hosts" class="nj-form-option" name="'.$field->get_name().'">' .html::specialchars($default).'</textarea>' ;
echo '</label>';
echo '</div>';

