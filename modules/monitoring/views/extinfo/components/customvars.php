<div class="information-component information-component-fullwidth">
	<div class="information-component-title">Custom Variables</div>
<?php
$variables = $object->get_public_custom_variables();
if ($variables) {
	foreach($variables as $variable => $value) {
?>
<div class="information-cell-inline">
	<div class="information-cell-header">_<?php echo html::specialchars($variable); ?></div>
	<div class="information-cell-raw faded"><?php echo link::linkify(security::xss_clean($value)) ?></div>
</div>
<?php
	}
}
?>
</div>
