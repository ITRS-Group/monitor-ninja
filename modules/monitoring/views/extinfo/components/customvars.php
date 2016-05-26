<div class="information-component information-component-fullwidth">
	<div class="information-component-title">Variables</div>
<?php
if ($object->get_custom_variables()) {
	foreach($object->get_custom_variables() as $variable => $value) {
	    if (substr($variable, 0, 6) !== 'OP5H_') { ?>
    <div class="information-cell-inline">
      <div class="information-cell-header">_<?php echo html::specialchars($variable); ?></div>
      <div class="information-cell-raw faded"><?php echo link::linkify(security::xss_clean($value)) ?></div>
    </div>
<?php
		}
	}
}
?>
</div>
