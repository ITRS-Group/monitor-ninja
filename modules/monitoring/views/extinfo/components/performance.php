<div class="information-component">
<div class="information-component-title">Performance</div>

<div class="information-performance-gauges">
<?php

$linkprovider = LinkProvider::factory();
$pnp_href = $object->graphs();
$perf_data = $object->get_perf_data();
$perf_data_class = new performance_data();
if(count($perf_data)) {
	$index = 0;
	foreach($perf_data as $ds_name => $ds) {

		$id = "graph-$index";
		if (!isset($ds['min'])) $ds['min'] = 0;
		if (!isset($ds['max'])) $ds['max'] = 0;

		if (!$ds['max']) {
			$ds['max'] = (float) max(
				isset($ds['value']) ? $ds['value'] : 0,
				isset($ds['warn']) ? $ds['warn'] : 0,
				isset($ds['crit']) ? $ds['crit'] : 0
			);
		}

		// Decide which css class to use to display the right color.
		if (empty($ds['crit']) && empty($ds['warn'])) {
			// Note that 0 is handled as if the value is not set.
			$class = 'no-threshold'; // No thresholds are set, show blue color.
		} else if (isset($ds['crit']) &&
			       $perf_data_class->match_threshold($ds['crit'], $ds['value'])) {
			$class = 'critical';
		} else if (isset($ds['warn']) &&
			       $perf_data_class->match_threshold($ds['warn'], $ds['value'])) {
			$class = 'warning';
		} else {
			$class = 'ok';
		}

		$unit = (isset($ds['unit'])) ? $ds['unit'] : 'value';
		$index++;

?>
	<a title="Go to PNP graphs for this object" href="<?php echo $pnp_href; ?>"
	   class="information-gauge">
		<span id="<?php echo $id; ?>" class="<?php echo $class; ?>"></span>
		<span><?php echo $ds_name; ?></span>
	</a>
		<script>
		(function () {
				var unit = "<?php echo $unit; ?>";
				var chart = c3.generate({
					bindto: "#<?php echo $id; ?>",
					data: {
						columns: [['<?php echo $ds_name; ?>', <?php echo "" . (isset($ds['value']))? $ds['value']: 0; ?>]],
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
						pattern: ['auto']
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
			<th><!-- State --></th>
			<th>Label</th>
			<th>Value</th>
			<th>UOM</th>
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
			if (isset($ds['crit']) && $perf_data_class->match_threshold($ds['crit'], $ds['value'])) {
				echo '<span class="icon-state-critical critical"></span>';
			} else if (isset($ds['warn']) && $perf_data_class->match_threshold($ds['warn'], $ds['value'])) {
				echo '<span class="icon-state-warning warning"></span>';
			}
?>
			</td>
			<td>
<?php
		echo html::specialchars($ds_name);
?>
			</td>
			<td>
<?php
		echo (isset($ds['value']) ? $ds['value'] : '');
?>
			</td>
			<td>
<?php
		echo (isset($ds['value']) ? (isset($ds['unit']) ? $ds['unit'] : '') : '');
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
		echo (isset($ds['min']) ? $ds['min'] : '');
?>
			</td>
			<td>
<?php
		echo (isset($ds['max']) ? $ds['max'] : '');
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
