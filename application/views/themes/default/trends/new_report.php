<?php

	$GRAPHS_T_WIDTH = 700;
		
	$HOST_STATES = array (
		"-1" => 'Undetermined',
		"0" => 'Up',
		"1" => 'Unreachable',
		"2" => 'Down'
	);
	
	$HOST_COLORS = array (
		"-1" => '#aaa',
		"0" => '#9e0',
		"1" => '#fb4',
		"2" => '#f50'
	);
	
?>
<br />

<?php if (!isset($is_avail)) { ?>

<?php
}
?>

<script>
	TGraphEventBinder(document.getElementById('print-report-pdf'), 'mousedown', function () {
		collapse_menu('hide', 1);
	});
</script>

<table>
<tr><th class="headerNone left">
Trends Graph - <?php echo $options['report_period'] ?>
</th></tr>

<tr class="even"><td>

<div id="trends_graphs" style="margin: 20px auto 0 auto;">
		
		<?php
				
				echo "<div id='tgraph'></div>";
				?>
				<script>
					
					<?php
					
						$str = '';
						$labels = '';
						
						foreach ($graph_pure_data as $service => $statechanges) {
						
							$str = $str.'[';
							$labels = $labels."'$service',";
							
							for ($i = 0; $i < count($statechanges); $i++) {
									
								$str = $str."{".
									"'duration': ".$statechanges[$i]['duration'].",".
									"'label': '".$HOST_STATES[$statechanges[$i]['state']]."',".
									"'color': '".$HOST_COLORS[$statechanges[$i]['state']]."',".
									"'short': '".addslashes($statechanges[$i]['output'])."'".
									"}";
									
								if ($i < count($statechanges) - 1) {
									$str = $str.',';
								}
								
							}
							
							$str = $str.'],';
							
						}
						
						$str = $str.substr(0, strlen($str) - 1);
						$labels = $labels.substr(0, strlen($labels) - 1);

					?>
					
					var data = [<?php echo $str; ?>],
						labels = [<?php echo $labels; ?>];

					new TGraph(
						data, 'timeline', 
						labels,
						<?php echo $graph_start_date ?>
					);
					
				</script>
				<?php
	?>

</div>

</td></tr></table>
<br />
<?php
/*
if (isset($graph_image_source) && $graph_image_source) { ?>
	<img src="<?php echo url::site() ?>public/<?php echo $graph_image_source ?>" alt="" />
<?php } ?>
<div style="clear:both"></div>
<?php if(isset($avail_template) && !empty($avail_template)) {
	echo $avail_template;
}*/
