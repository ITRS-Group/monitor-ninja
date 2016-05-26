<div class="information-component information-service-matrix">
	<div class="information-component-title">Service states</div>
	<div class="information-cell-header">
		Total: <a href="<?php echo listview::querylink('[services] host.name="' . $object->get_name() . '"'); ?>"><em><?php echo $object->get_num_services(); ?></em> services</a>
	</div>
	<br />

	<?php if ($object->get_num_services_ok() > 0) { ?>
	<a title="Go to list of services on this host in state ok" href="<?php echo listview::querylink('[services] host.name="' . $object->get_name() . '" and state = 0'); ?>">
	<div class="information-cell big ok state-background">
		<div class="information-cell-header">Ok</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_ok(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_services_warn() > 0) { ?>
	<a title="Go to list of services on this host in state warning" href="<?php echo listview::querylink('[services] host.name="' . $object->get_name() . '" and state = 1'); ?>">
	<div class="information-cell big warning state-background">
		<div class="information-cell-header">Warning</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_warn(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_services_crit() > 0) { ?>
	<a title="Go to list of services on this host in state critical" href="<?php echo listview::querylink('[services] host.name="' . $object->get_name() . '" and state = 2'); ?>">
	<div class="information-cell big critical state-background">
		<div class="information-cell-header">Critical</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_crit(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_services_unknown() > 0) { ?>
	<a title="Go to list of services on this host in state unknown" href="<?php echo listview::querylink('[services] host.name="' . $object->get_name() . '" and state = 3'); ?>">
	<div class="information-cell big unknown state-background">
		<div class="information-cell-header">Unknown</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_unknown(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_services_pending() > 0) { ?>
	<a title="Go to list of services on this host in state pending" href="<?php echo listview::querylink('[services] host.name="' . $object->get_name() . '" and state = 4'); ?>">
	<div class="information-cell big pending state-background">
		<div class="information-cell-header">Pending</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_pending(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

</div>
<?php
