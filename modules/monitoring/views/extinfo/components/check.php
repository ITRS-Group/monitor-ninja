<div class="information-component">
	<div class="information-component-title">
		Information regarding last check
	</div>
	<div class="information-cell" title="Time between scheduled time and the actual execution of the check">
		<div class="information-cell-header">
			<?php echo _('Latency'); ?>
		</div>
		<div class="information-cell-value">
			<?php echo number_format($object->get_latency(), 2); ?>sec
		</div>
	</div>
	<div class="information-cell" title="The time it took for the monitoring core to execute the check">
		<div class="information-cell-header">
			<?php echo _('Duration'); ?>
		</div>
		<div class="information-cell-value">
			<?php echo number_format($object->get_execution_time(), 2); ?>sec
		</div>
	</div>
		<?php
			$type = $object->get_check_type_str();
			$check_type_title = 'N/A';
			if ($type === 'active') {
				$check_type_title = 'This was a scheduled monitoring check performed automatically';
			} elseif ($type === 'passive') {
				$check_type_title = 'This was a passively submitted check result, e.g. a monitoring agent or a user command';
			}
		?>
	<div class="information-cell" title="<?php echo $check_type_title; ?>">
		<div class="information-cell-header">
			<?php echo _('Type'); ?>
		</div>
		<div class="information-cell-value">
			<?php echo $type; ?>
		</div>
	</div>
	<div class="information-cell" title="The source node of the check result, mainly relevant in distributed configurations">
		<div class="information-cell-header">
			<?php echo _('Source'); ?>
		</div>
		<div class="information-cell-value" >
			<?php echo $object->get_source_node(); ?>
			<?php echo '(' . $object->get_source_type() . ')'; ?></div>
	</div>
</div>
