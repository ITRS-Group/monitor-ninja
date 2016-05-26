<div class="information-component">
<div class="information-component-title">Performance</div>

<div class="information-performance-gauges">
<?php

$perf_data = $object->get_perf_data();
if(count($perf_data)) {
	foreach($perf_data as $ds_name => $ds) {

		if (!isset($ds['min'])) $ds['min'] = 0;
		if (!isset($ds['max'])) $ds['max'] = 0;
		if (!isset($ds['warn'])) $ds['warn'] = 0;
		if (!isset($ds['crit'])) $ds['crit'] = 0;

		$ds['max'] = ($ds['max']) ? $ds['max'] : max((float)$ds['value'], (float)$ds['warn'], (float)$ds['crit']);

		$id = "graph-$ds_name";
		$ds_name = performance_data::get_readable_name($ds_name);

		$ds['warn'] = (float)$ds['warn'];
		$ds['crit'] = (float)$ds['crit'];
		$unit = (isset($ds['unit'])) ? $ds['unit'] : 'value';

?>
	<span class="information-gauge">
		<span id="<?php echo $id; ?>"></span>
		<span><?php echo $ds_name; ?></span>
	</span>
		<script>
		(function () {
				var unit = "<?php echo $unit; ?>";
				var chart = c3.generate({
					bindto: "#<?php echo $id; ?>",
					data: {
						columns: [['<?php echo $ds_name; ?>', <?php echo $ds['value']; ?>]],
						type: "gauge"
					},
					gauge: {
						label: {
							show: true,
							format: function (value, ratio) {
								if (unit === '%') {
									return (100 * ratio) + '%';
								}
								return value;
							}
						},
						min: <?php echo $ds['min']; ?>,
						max: <?php echo $ds['max']; ?>
					},
					color: {
						pattern: ['#f05051','#fad546','#82cd60'],
						threshold: {
							unit: unit,
							values: [<?php echo $ds['crit']; ?>, <?php echo $ds['warn']; ?>]
						}
					},
					size: {
						height: 90
					}
				});
			})();
		</script>
		<?php

	}
} else {
?>
	<p class="faded">No performance data available</p>
	<?php
}
?>
</div>
</div>
