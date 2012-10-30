
<?php if (!isset($is_avail)) { ?>

<?php
}
?>


<div id="trends_graphs" style="margin: 20px auto 0 auto;">
	<div id='tgraph'>Trend graphs loading...</div>
	<script>
		<?php

			$rawdata = array();
			$labels = array();
			foreach ($graph_pure_data as $service => $statechanges) {
			
				$labels[] = $service;
				
				$servicerow = array();
				
				for ($i = 0; $i < count($statechanges); $i++) {
					
					$type = $statechanges[$i]['object_type'];
														
					$servicerow[] = array(
						$statechanges[$i]['duration'], /* 0: Duration */
						ucfirst($this->_state_string_name($type, $statechanges[$i]['state'])), /* 1: Label */
						$statechanges[$i]['output'] /* 2: short */
						);
					
				}
				$rawdata[] = $servicerow;
				
			}

			$colors = array();
			foreach( $this->_state_color_table($type) as $state => $color ) {
				$colors[ucfirst($this->_state_string_name($type,$state))] = $color;
			}
		?>
		
		var rawdata = <?php echo json_encode($rawdata); ?>,
			labels = <?php echo json_encode($labels); ?>,
			colors = <?php echo json_encode($colors); ?>;


		var data = [];
		for( var i=0; i<rawdata.length; i++ ) {
			data[i] = [];
			for( var j=0; j<rawdata[i].length; j++ ) {
				data[i][j] = {
					'duration': rawdata[i][j][0],
					'label': rawdata[i][j][1],
					'short': rawdata[i][j][2],
					'color': colors[rawdata[i][j][1]]
				};
			}
		}
		
		$(window).load(function () {
			new TGraph(
				data, 'timeline', 
				labels,
				<?php echo $graph_start_date ?>,
				<?php echo ($use_scaling) ? 'true':'false'; ?>
			);
		});
		
	</script>
</div>
