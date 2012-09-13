<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"></div>
<div id="progress"></div>
<div class="report-page">
<?php
	echo isset($error) ? $error : '';
	echo !empty($header) ? $header : '';
?>
<div style="display: none">
<div id="options">
<?php echo form::open($type.'/generate', array('id' => 'report_form', 'onsubmit' => 'return validate_report_form(this);'));?>
<?php
	echo $report_options;
	echo $options->as_form(false, true);
?>
</form>
</div>
</div>
<?php
	if (isset($links)) {
		echo '<br /><br />'._('View').': ';
		$html_links = array();
		foreach($links as $url => $name) {
			$html_links[] = html::anchor(url::site($url),html::image($this->add_path('/icons/16x16/'.strtolower(str_replace(' ','-',$name))).'.png',array('alt' => $name, 'title' => $name, 'style' => 'margin-bottom: -3px')),array('style' => 'border: 0px')).
			' <a href="'.url::site($url).'">'.$name.'</a>';
		}
		echo implode(', &nbsp;', $html_links);
	}
	if (!empty($trends_graph)) {
		echo '<strong style="margin-top: 25px;display: block">'.help::render('trends').' '._('Trends').'</strong>';
		echo $trends_graph;
	}
	if (!empty($content)) {
		echo $content;
		echo !empty($svc_content) ? $svc_content : '';
		echo isset($pie) ? $pie : '';
		echo !empty($log_content) ? $log_content : '';
	}
?>
</div>
