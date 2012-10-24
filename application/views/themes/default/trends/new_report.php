
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
																	
								$str = $str."{".
									"'duration': ".$statechanges[$i]['duration'].",".
									"'label': '".ucfirst($this->_state_string_name($type, $statechanges[$i]['state']))."',".
									"'color': '".$this->_state_colors($type, $statechanges[$i]['state'])."',".
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
						<?php echo $graph_start_date ?>,
						<?php echo ($use_scaling) ? 'true':'false'; ?>
					);
					
				</script>
				<?php
	?>
</div>
