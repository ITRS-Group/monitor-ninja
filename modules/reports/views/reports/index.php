<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"></div>
<div id="progress"></div>
<div class="report-page">
<?php
	echo isset($error) ? $error : '';
	echo $header;
?>
<div style="display: none">
<div id="options">
<?php echo form::open($type.'/generate', array('class' => 'report_form'));?>
<?php
	echo $report_options;
?>
</form>
</div>
</div>
<?php
	if (isset($links)) {
		echo '<div class="report-block" id="report-links-internal">';
		echo _('View').': ';
		$html_links = array();
		foreach($links as $url => $name) {
			$html_links[] = html::anchor(
				url::site($url),
				html::image(
					sprintf(
						$this->add_path('/icons/16x16/%s'),
						strtolower(str_replace(' ','-',$name)).'.png'
					),
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
		echo $content;
		echo !empty($svc_content) ? $svc_content : '';
		echo isset($pie) ? $pie : '';
		echo !empty($log_content) ? $log_content : '';
		echo !empty($synergy_content) ? $synergy_content : '';
		echo isset($extra_content) ? $extra_content: '';
	}
?>
</div>
