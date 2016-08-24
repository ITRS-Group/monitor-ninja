<div class="information-component">
<div class="information-component-title">Performance</div>

<div class="information-performance-gauges">
<?php

$linkprovider = LinkProvider::factory();
$perf_data = $object->get_perf_data();
if(count($perf_data)) {
	$index = 0;
	foreach($perf_data as $ds_name => $ds) {

		$id = "graph-$index";

		if (!isset($ds['min'])) $ds['min'] = 0;
		if (!isset($ds['max'])) $ds['max'] = 0;
		if (!isset($ds['warn'])) $ds['warn'] = 0;
		if (!isset($ds['crit'])) $ds['crit'] = 0;

		if (!$ds['max']) {
			$ds['max'] =  max(
				(float)$ds['value'],
				(float)$ds['warn'],
				(float)$ds['crit']
			);
		}

		$pnp_href = $object->graphs();

		$ds['warn'] = (float)$ds['warn'];
		$ds['crit'] = (float)$ds['crit'];

		if (!$ds['crit'])
			$ds['crit'] = $ds['max'];

		$unit = (isset($ds['unit'])) ? $ds['unit'] : 'value';
		$index++;

?>
	<a title="Go to PNP graphs for this object" href="<?php echo $pnp_href; ?>" class="information-gauge">
		<span id="<?php echo $id; ?>"></span>
		<span><?php echo $ds_name; ?></span>
	</a>
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
									return (100 * ratio).toFixed(2) + '%';
								}
								return value;
							}
						},
						min: <?php echo $ds['min']; ?>,
						max: <?php echo $ds['max']; ?>
					},
					color: {
						pattern: ['#82cd60','#fad546','#f05051'],
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
<?php
if(count($perf_data)) {
?>
<div class="information-performance-raw">
	<span class="information-performance-raw-show">
		[<?php echo icon::get('list-alt'); ?>
		<span class="information-performance-raw-show-label">Show raw data</span> ]
	</span>
	<table>
		<tr>
			<th>Label</th>
			<th>Value</th>
			<th>Warning</th>
			<th>Critical</th>
			<th>Min</th>
			<th>Max</th>
		</tr>
<?php
	foreach($perf_data as $ds_name => $ds) {
?>
		<tr>
			<td>
<?php
		echo html::specialchars($ds_name);
?>
			</td>
			<td>
<?php
		echo (isset($ds['value']) ? $ds['value'] : '') . (isset($ds['unit']) ? $ds['unit'] : '');
?>
			</td>
			<td>
<?php
		echo (isset($ds['warn']) ? $ds['warn'] : '');
?>
			</td>
			<td>
<?php
		echo (isset($ds['crit']) ? $ds['crit'] : '');
?>
			</td>
			<td>
<?php
		echo (isset($ds['min']) ? $ds['min'] : '') . (isset($ds['unit']) ? $ds['unit'] : '');
?>
			</td>
			<td>
<?php
		echo (isset($ds['max']) ? $ds['max'] : '') . (isset($ds['unit']) ? $ds['unit'] : '');
?>
			</td>
		</tr>
<?php
	}
?>
	</table>
</div>
<?php
}
?>
</div>
