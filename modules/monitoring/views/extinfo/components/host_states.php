<div class="information-component information-service-matrix">
	<div class="information-component-title">Host states</div>

	<?php if ($object->get_num_hosts_up() > 0) { ?>
	<a title="Go to list of hosts in this hostgroup with state up" href="<?php echo listview::querylink('[hosts] groups>="' . $object->get_name() . '" and state = 0'); ?>">
	<div class="information-cell big ok state-background">
		<div class="information-cell-header">Up</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_hosts_up(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_hosts_down() > 0) { ?>
	<a title="Go to list of hosts in this hostgroup with state down" href="<?php echo listview::querylink('[hosts] groups>="' . $object->get_name() . '" and state = 1'); ?>">
	<div class="information-cell big down state-background">
		<div class="information-cell-header">Down</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_hosts_down(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_hosts_unreach() > 0) { ?>
	<a title="Go to list of hosts in this hostgroup with state unreachable" href="<?php echo listview::querylink('[hosts] groups>="' . $object->get_name() . '" and state = 2'); ?>">
	<div class="information-cell big unreachable state-background">
		<div class="information-cell-header">Unreachable</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_hosts_unreach(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_hosts_pending() > 0) { ?>
	<a title="Go to list of hosts in this hostgroup with state pending" href="<?php echo listview::querylink('[hosts] groups>="' . $object->get_name() . '" and state = 3'); ?>">
	<div class="information-cell big pending state-background">
		<div class="information-cell-header">Pending</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_hosts_pending(); ?>
		</div>
	</div>
	</a>
	<?php } ?>
	<br /><br />
	<div class="information-cell-header">
		Total: <a href="<?php echo listview::querylink('[hosts] groups>="' . $object->get_name() . '"'); ?>"><em><?php echo $object->get_num_hosts(); ?></em> hosts</a>
	</div>
</div>
<?php
