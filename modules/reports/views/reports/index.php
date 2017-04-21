<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="report-page">
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
