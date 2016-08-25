<div class="information-component information-service-matrix">
	<div class="information-component-title">Service states</div>

<?php
		$base_set = ServicePool_Model::all();
		if ($object->get_table() === 'hosts') {
			$base_set = $base_set->reduce_by('host.name', $object->get_name(), '=');
		} else if ($object->get_table() === 'hostgroups') {
			$base_set = $base_set->reduce_by('host.groups', $object->get_name(), '>=');
		} else if ($object->get_table() === 'servicegroups') {
			$base_set = $base_set->reduce_by('groups', $object->get_name(), '>=');
		} else {
			?>
			<div class="information-cell">
				    <div class="information-cell-content">
				        Cannot render service states component for object type "<?php echo $object->get_table(); ?>"
				    </div>
				</div>
<?php
			return;
		}
	?>

	<?php if ($object->get_num_services_ok() > 0) {
		$query = $base_set->reduce_by('state', 0, '=')->get_query();
	?>
	<a title="Go to list of services on this host in state ok" href="<?php echo listview::querylink($query); ?>">
	<div class="information-cell big ok state-background">
		<div class="information-cell-header">OK</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_ok(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_services_warn() > 0) {
		$query = $base_set->reduce_by('state', 1, '=')->get_query();
	?>
	<a title="Go to list of services on this host in state warning" href="<?php echo listview::querylink($query); ?>">
	<div class="information-cell big warning state-background">
		<div class="information-cell-header">Warning</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_warn(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_services_crit() > 0) {
		$query = $base_set->reduce_by('state', 2, '=')->get_query();
	?>
	<a title="Go to list of services on this host in state critical" href="<?php echo listview::querylink($query); ?>">
	<div class="information-cell big critical state-background">
		<div class="information-cell-header">Critical</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_crit(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_services_unknown() > 0) {
		$query = $base_set->reduce_by('state', 3, '=')->get_query();
	?>
	<a title="Go to list of services on this host in state unknown" href="<?php echo listview::querylink($query); ?>">
	<div class="information-cell big unknown state-background">
		<div class="information-cell-header">Unknown</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_unknown(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_services_pending() > 0) {
		$query = $base_set->reduce_by('state', 4, '=')->get_query();
	?>
	<a title="Go to list of services on this host in state pending" href="<?php echo listview::querylink($query); ?>">
	<div class="information-cell big pending state-background">
		<div class="information-cell-header">Pending</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_services_pending(); ?>
		</div>
	</div>
	</a>
	<?php } ?>
	<br /><br />
	<div class="information-cell-header">
<?php
		$query = $base_set->get_query();
?>
Total: <a title="Go to list of all services on this host" href="<?php echo listview::querylink($query); ?>"><em><?php echo $object->get_num_services(); ?></em> services</a>
	</div>
</div>
<?php
