<?php

	$HOST_STATES = array (
		"-2" => 'Excluded',
		"-1" => 'Pending',
		"0" => 'Up',
		"1" => 'Down',
		"2" => 'Unreachable',
		"7" => 'All',
	);
	
	$HOST_COLORS = array (
		"-2" => '#fff',
		"-1" => '#a19e95',
		"0" => '#aade53',
		"1" => '#f7261b',
		"2" => '#ffd92f',
		"7" => '#f4d',
	);
	
	$SERVICE_STATES = array (
		"-2" => 'Excluded',
		"-1" => 'Pending',
		"0" => 'Ok',
		"1" => 'Warning',
		"2" => 'Critical',
		"3" => 'Unknown',
		"15" => 'All',
	);
	
	$SERVICE_COLORS = array (
		"-2" => '#fff',
		"-1" => '#a19e95',
		"0" => '#aade53',
		"1" => '#ffd92f',
		"2" => '#f7261b',
		"3" => '#ff9d08',
		"15" => '#f4d',
	);
	
?>


<br />

<?php if (!isset($is_avail)) { ?>

<?php
}
?>


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
								
								$type = $statechanges[$i]['object_type'];
									
								$color = ($type === 'host') ? 
									$HOST_COLORS[$statechanges[$i]['state']]: 
									$SERVICE_COLORS[$statechanges[$i]['state']];
								
								$label = ($type === 'host') ? 
									$HOST_STATES[$statechanges[$i]['state']]: 
									$SERVICE_STATES[$statechanges[$i]['state']];
																	
								$str = $str."{".
									"'duration': ".$statechanges[$i]['duration'].",".
									"'label': '".$label."',".
									"'color': '".$color."',".
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
