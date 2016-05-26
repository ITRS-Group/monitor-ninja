<div class="information-component">
	<div class="information-component-title">
		Information regarding last check
	</div>
	<div class="information-cell">
		<div class="information-cell-header">
			<?php echo _('Latency'); ?>
		</div>
		<div class="information-cell-value">
			<?php echo number_format($object->get_latency(), 2); ?>sec
		</div>
	</div>
	<div class="information-cell">
		<div class="information-cell-header">
			<?php echo _('Duration'); ?>
		</div>
		<div class="information-cell-value">
			<?php echo number_format($object->get_execution_time(), 2); ?>sec
		</div>
	</div>
	<div class="information-cell">
		<div class="information-cell-header">
			<?php echo _('Type'); ?>
		</div>
		<div class="information-cell-value">
			<?php echo $object->get_check_type_str(); ?>
		</div>
	</div>
	<div class="information-cell">
		<div class="information-cell-header">
			<?php echo _('Source'); ?>
		</div>
		<div class="information-cell-value">
			<?php echo $object->get_source_node(); ?>
			(<?php echo $object->get_source_type(); ?>)
		</div>
	</div>
</div>
