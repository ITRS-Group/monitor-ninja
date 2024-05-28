<div class="report-block">
<?php
    $rawdata = array();
    $labels = array();
    $state_names = array();
    $outputs = array();
    $outputs_r = array();

    foreach($graph_pure_data as $service => $statechanges) {
        $labels[] = $service;
        $servicerow = array();

        for($i = 0; $i < count($statechanges); $i++) {
            $cur_out = $statechanges[$i]['output'];

            if(isset($outputs_r[$cur_out])) {
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

    $data = array();
    for($i = 0; $i < count($rawdata); $i++) {
        $data[$i] = array();
        for ($j = 0; $j < count($rawdata[$i]); $j++) {
            $data[$i][$j] = array(
                'duration' => $rawdata[$i][$j][0],
                'label' => $state_names[$rawdata[$i][$j][1]],
                'short' => str_replace('\'', '', $outputs[$rawdata[$i][$j][2]]),
                'color' => $colors[$rawdata[$i][$j][1]]
            );
        }
    }

	$lastHost = '';
	function generateDates($startDate, $endDate) {
		$dates = array();
		$start = new DateTime(date("Y-m-d H:i:s",$startDate));
		$end = new DateTime(date("Y-m-d H:i:s",$endDate));
		$end->modify('-1 days');
		$interval = $start->diff($end);
		$daysDifference = $interval->days;

		// Calculate the interval depending on the span of days
		$intervalDays = ($daysDifference <= 7) ? 1 : ceil($daysDifference / 7);
		// Generate dates based on the calculated interval
		while($start <= $end) {
			$dates[] = $start->format('Y-m-d');
			$start->modify('+' . $intervalDays . ' days');
		}
		return $dates;
	}

	$dateLabel = generateDates($graph_start_date, $graph_end_date);
    $totalInterval = $graph_end_date - $graph_start_date;
    $dateCount = count($dateLabel);
    $labelCount = count($labels);

	for($y = 0; $y < $labelCount; $y++) {
		// graph for Y-labels
		if(mb_strpos($labels[$y], ';')) {
			$Hostname = explode(';', $labels[$y])[0];
			$Servicename = explode(';', $labels[$y])[1];
			if ($Hostname === $lastHost) {
				$rowLabel = $Servicename;
			} else {
				$rowLabel = "<strong>".$Hostname."</strong>;".$Servicename;
			}
			$lastHost = $Hostname;
		} else {
			$rowLabel = "<strong>".$labels[$y]."</strong>";
		}

        $bars = count($data[$y])-1;
		echo "<div class='x-item'>";
			echo "<label class='y-label'>".$rowLabel."</label>";
			echo "<div class='tgraph-row'>"; 
                if($use_scaling) {
                    $scaleLastDT = new DateTime(date("Y-m-d H:i:s", $graph_end_date));
                    $scaleStartDT =  new DateTime(date("Y-m-d H:i:s", $graph_end_date));

                    echo "<div class='tgraph-sub-blockline'>";
                        for($z = $bars; $z >= 0; $z--){
                            $barAve = round($data[$y][$z]['duration'] / $totalInterval,3);
                            $barWidth = round($barAve*100);
                            $scaleStartDT->modify('-'.$data[$y][$z]['duration'].' second');
                            $dataValue = "<br />".$scaleStartDT->format('M d, Y h:i a')." to ".$scaleLastDT->format('M d, Y h:i a')."<br />".$data[$y][$z]['short'];

                            if($barAve < 0.03) {
                                echo "<div class='bar' data-label='".$data[$y][$z]['label']."' data-value='".$dataValue."' id='bar' style='width:".$barWidth."%; background: ".$data[$y][$z]['color'].";'></div>";

                                $data[$y][$z]['color'] = "#333";
                            } else {
                                echo "<div class='bar-transparent' style='width:".$barWidth."%;'></div>";
                            }
                            $scaleLastDT->modify('-'.$data[$y][$z]['duration'].' second');
                        }
                    echo "</div>";
                }

                echo "<div class='tgraph-blockline'>";
                    $lastDateTime = new DateTime(date("Y-m-d H:i:s", $graph_end_date));
                    $startDateTime = new DateTime(date("Y-m-d H:i:s", $graph_end_date));

                    for($z = $bars; $z >= 0; $z--){
                        $barAve = round($data[$y][$z]['duration'] / $totalInterval,3);
                        $barWidth = round($barAve*100);
                        $startDateTime->modify('-'.$data[$y][$z]['duration'].' second');
                        $dataValue = "<br />".$startDateTime->format('M d, Y h:i a')." to ".$lastDateTime->format('M d, Y h:i a')."<br />".$data[$y][$z]['short'];

                        echo "<div class='bar' data-label='".$data[$y][$z]['label']."' data-value='".$dataValue."' id='bar' style='width:".$barWidth."%; background: ".$data[$y][$z]['color'].";'></div>";
                        
                        $lastDateTime->modify('-'.$data[$y][$z]['duration'].' second');
                    }
                echo "</div>";
			echo "</div>";
		echo "</div>";
	}

    echo "<div id='tooltip'></div>";
    echo "<div style='clear: both;'></div>";
    echo "<label class='y-label'></label>";
    echo "<div class='tgraph-row'>";
        echo "<div style='clear: both;'></div>";
        // graph X-labels
        $i = 0;
        while($i < $dateCount) {
            echo "<div class='tgraph-time' style='width: ".(100/$dateCount)."%';'>".
                   "<div class='tgraph-timeline' style='height: ".($labelCount*40)."px; margin-top: -".($labelCount*40)."px;'></div>".
                   "&nbsp;&nbsp;".$dateLabel[$i].
                 "</div>";
            $i++;
        }
    echo "</div>";
?>

<?php if($included && $skipped) {
    printf(_("<p>Not showing %d graphs due to being 100%% OK</p>"), $skipped);
} ?>
</div>

<script>
    // JavaScript for hover interaction
    var tooltip = document.getElementById('tooltip');
    var bars = document.querySelectorAll(".bar");

    bars.forEach(function(bar) {
        bar.addEventListener('mouseover', function(e) {
            var label = e.target.getAttribute('data-label');
            var value = e.target.getAttribute('data-value');
            tooltip.style.display = 'block';
            tooltip.style.left = (e.pageX + 10) + 'px';
            tooltip.style.top = (e.pageY + 10) + 'px';
            tooltip.innerHTML = '<b>'+ label +'</b>: '+ value;
        });
        bar.addEventListener('mouseout', function(e) {
            tooltip.style.display = 'none';
        });
    });
</script>
