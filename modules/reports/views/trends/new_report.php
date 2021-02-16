<div id="trends_graphs" style="margin: 20px auto 0 auto;">
	<div id='tgraph'>Trend graphs loading...</div>
	<script>
		<?php

			$rawdata = array();
			$labels = array();
			$state_names = array();
			$outputs = array();
			$outputs_r = array();

			foreach ($graph_pure_data as $service => $statechanges) {

				$labels[] = $service;

				$servicerow = array();

				for ($i = 0; $i < count($statechanges); $i++) {

					$cur_out = $statechanges[$i]['output'];
					if( isset( $outputs_r[$cur_out] ) ) {
						$output_id = $outputs_r[$cur_out];
					} else {
						$output_id = count($outputs);
						$outputs[] = $cur_out;
						$outputs_r[$cur_out] = $output_id;
					}

					$servicerow[] = array(
						$statechanges[$i]['duration'], /* 0: duration */
						$statechanges[$i]['state'], /* 1: state */
						$output_id /* 2: short */
						);

					switch(strtolower($obj_type)) {
						case 'host':
							$state_name = Reports_Model::$host_states[$statechanges[$i]['state']];
							break;
						case 'service':
							$state_name = Reports_Model::$service_states[$statechanges[$i]['state']];
							break;
						default:
							$state_name = 'N/A';
					}
					$state_names[$statechanges[$i]['state']] = ucfirst($state_name);

				}
				$rawdata[] = $servicerow;

			}
			$colors = reports::_state_color_table($obj_type);

		?>


		var rawdata = <?php echo json_encode($rawdata); ?>,
			short_names = <?php echo json_encode($outputs); ?>,
			labels = <?php echo json_encode($labels); ?>,
			state_names = <?php echo json_encode($state_names); ?>,
			colors = <?php echo json_encode($colors); ?>;


		var data = [];
		for( var i=0; i<rawdata.length; i++ ) {
			data[i] = [];
			for( var j=0; j<rawdata[i].length; j++ ) {
				data[i][j] = {
					'duration': rawdata[i][j][0],
					'label': state_names[rawdata[i][j][1]],
					'short': short_names[rawdata[i][j][2]],
					'color': colors[rawdata[i][j][1]]
				};
			}
		}

		$(window).on('load', function () {
			new TGraph(
				data, 'timeline',
				labels,
				<?php echo $graph_start_date ?>,
				<?php echo ($use_scaling) ? 'true':'false'; ?>
			);
		});

	</script>
	<?php if ($included && $skipped) {
		printf(_("<p>Not showing %d graphs due to being 100%% OK</p>"), $skipped);
	} ?>
</div>
