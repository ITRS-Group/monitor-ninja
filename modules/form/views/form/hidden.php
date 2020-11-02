<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Hidden_Model */

$default = $form->get_value($field->get_name(), "");
$element_id = 'element_id_'.uniqid();

?>

<input type="hidden" class="nj-form-option" id="<?php echo $element_id; ?>" name="<?php echo html::specialchars($field->get_name()); ?>" value="<?php echo html::specialchars($default); ?>">
