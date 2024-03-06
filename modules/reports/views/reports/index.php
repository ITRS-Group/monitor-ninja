<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="report-page" name="report-page" class="report-page">
<?php
	echo isset($error) ? $error : '';
	if ($header instanceof View) {
		$header->render(true);
	} else {
		// If $header is something that can be printable
		echo $header;
	}
?>
<?php
	if (isset($links)) {
		echo '<div class="report-block" id="report-links-internal">';
		echo _('View').': ';
		$html_links = array();
		foreach($links as $url => $name) {
			$html_links[] = html::anchor(
				url::site($url),
				html::image(
					ninja::add_path(sprintf(
						'/icons/16x16/%s',
						strtolower(str_replace(' ','-',$name)).'.png'
					)),
					array(
						'alt' => $name,
						'title' => $name,
						'style' => 'margin-bottom: -3px; width: 16px; height: 16px;'
					)
				),
				array('style' => 'border: 0px')).
			' <a href="'.url::site($url).'">'.$name.'</a>';
		}
		echo implode(', &nbsp;', $html_links);
		echo '</div>';
	}
	if (!empty($trends_graph)) {
		echo '<div class="report-block">'.help::render('trends').' '._('Trends');
		echo $trends_graph;
		echo '</div>';
	}
	if (!empty($content)) {
		if ($content instanceof View) {
			$content->render(true);
		} else {
			// If $header is something that can be printable
			echo $content;
		}
		echo !empty($svc_content) ? $svc_content : '';
		echo isset($pie) ? $pie : '';
		echo !empty($log_content) ? $log_content : '';
		echo !empty($synergy_content) ? $synergy_content : '';
		echo isset($extra_content) ? $extra_content: '';
	}
?>
</div>
<script>
	// this will capture rendered output as html strings
	function captureReport() {
		var htmlContent = document.getElementById('report-page').innerHTML;
		// working on captured html string will match the displayed data to the pdf to be generated
		// and this will also remove the use of javascripts that will avoid missing components on the pdf
		console.log(htmlContent);

		// using ajax to submit client-side variables to the controller
		var xhr = new XMLHttpRequest();
		<?php 
			$reportcontroller = Kohana::find_file('controllers','base_reports');
			// ninja/modules/reports/controllers/base_reports.php
		?>
		xhr.open('POST', '<?php echo json_encode($reportcontroller);?>', true); // checking if this needs an update
		xhr.setRequestHeader('Content-Type', 'application/json');
		xhr.onreadystatechange = function() {
			if (xhr.status == 200) {
				console.log('Submit successful');
			} else {
				console.log('Status:'+xhr.status); // currently results to 403 (FORBIDDEN)
			}
		}; 
		xhr.send('htmlContent=' + encodeURIComponent(htmlContent));
	}

	$(window).on("load", function () {
		captureReport();
	});
</script>
