<div class="information-component information-service-matrix">
	<div class="information-component-title">Host states</div>
<?php
$base_set = HostPool_Model::all();
if ($object->get_table() === 'hostgroups') {
	$base_set = $base_set->reduce_by('host.groups', $object->get_name(), '>=');
} else {
?>
<div class="information-cell">
	<div class="information-cell-content">
		Cannot render host states component for object type "<?php echo $object->get_table(); ?>"
	</div>
</div>
<?php
	return;
}
?>

<?php if ($object->get_num_hosts_up() > 0) {
		$query = $base_set->reduce_by('state', 0, '=')->get_query();
	?>
	<a title="Go to list of hosts in this hostgroup with state up" href="<?php echo listview::querylink($query); ?>">
	<div class="information-cell big ok state-background">
		<div class="information-cell-header">Up</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_hosts_up(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_hosts_down() > 0) {
		$query = $base_set->reduce_by('state', 1, '=')->get_query();
	?>
	<a title="Go to list of hosts in this hostgroup with state down" href="<?php echo listview::querylink($query); ?>">
	<div class="information-cell big down state-background">
		<div class="information-cell-header">Down</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_hosts_down(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_hosts_unreach() > 0) {
		$query = $base_set->reduce_by('state', 2, '=')->get_query();
	?>
	<a title="Go to list of hosts in this hostgroup with state unreachable" href="<?php echo listview::querylink($query); ?>">
	<div class="information-cell big unreachable state-background">
		<div class="information-cell-header">Unreachable</div>
		<div class="information-cell-content">
			<?php echo $object->get_num_hosts_unreach(); ?>
		</div>
	</div>
	</a>
	<?php } ?>

	<?php if ($object->get_num_hosts_pending() > 0) {
		$query = $base_set->reduce_by('state', 3, '=')->get_query();
	?>
	<a title="Go to list of hosts in this hostgroup with state pending" href="<?php echo listview::querylink($query); ?>">
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
<?php
		$query = $base_set->get_query();
?>
		Total: <a href="<?php echo listview::querylink($query); ?>"><em><?php echo $object->get_num_hosts(); ?></em> hosts</a>
	</div>
</div>
<?php
