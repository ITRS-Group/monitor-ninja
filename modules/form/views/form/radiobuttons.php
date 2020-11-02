<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="nj-form-field">
    <div class="nj-form-label">
        <?php echo html::specialchars($field->get_pretty_name()); ?>
    </div>
    <div class="nj-form-field-radio-buttons">
<?php
    foreach ( $field->get_options() as $option_value => $label ) {
        /* Order of input and nj-form-label matters! */
        View::factory('form/radio', array(
            "name" => $field->get_name(),
            "value" => $option_value,
            "label" => $label,
            "selected" => ($value === $option_value)
        ))->render(true);
    }
?>
    </div>
</div>

