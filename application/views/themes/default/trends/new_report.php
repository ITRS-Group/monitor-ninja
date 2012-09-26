<?php

	$GRAPHS_T_WIDTH = 700;
		
	$STATES = array (
		"-1" => 'Undetermined',
		"0" => 'Up',
		"1" => 'Unreachable',
		"2" => 'Down'
	);
	
	$COLORS = array (
		"-1" => '#aaa',
		"0" => '#9e0',
		"1" => '#fb4',
		"2" => '#f50'
	);
	
?>
<br />

<?php if (!isset($is_avail)) { ?>
<p><?php echo _('Reporting period') ?>: <?php echo $options['report_period'] ?></p>
<?php
}
?>

<script>
	TGraphEventBinder(document.getElementById('print-report-pdf'), 'mousedown', function () {
		collapse_menu('hide', 1);
	});
</script>


<div id="trends_graphs" style="margin: 20px auto; width: 80%;">
		
		<?php
		
			foreach ($graph_pure_data as $service => $statechanges) {
				echo "<div id='tgraph-$service'></div>";
				?>
				<script>
					document.getElementById('tgraph-<?php echo $service; ?>').appendChild(
						new TGraph([
							<?php
								for ($i = 0; $i < count($statechanges); $i++) {
								
									echo "{".
										"'duration': ".$statechanges[$i]['duration'].",".
										"'label': '".$STATES[$statechanges[$i]['state']]."',".
										"'color': '".$COLORS[$statechanges[$i]['state']]."',".
										"'short': '".addslashes($statechanges[$i]['output'])."'".
										"}";
										
									if ($i < count($statechanges) - 1) {
										echo ',';
									}
								}
								?>
							], 'timeline', 
							'<?php echo $service; ?>',
							<?php echo $GRAPHS_T_WIDTH - 100; ?>, 
							'<?php echo date('Y-m-d  H:i:s', $graph_start_date) ?>'
						)
					);
				</script>
				<?php
			}
	?>

</div>

<?php
/*
if (isset($graph_image_source) && $graph_image_source) { ?>
	<img src="<?php echo url::site() ?>public/<?php echo $graph_image_source ?>" alt="" />
<?php } ?>
<div style="clear:both"></div>
<?php if(isset($avail_template) && !empty($avail_template)) {
	echo $avail_template;
}*/
