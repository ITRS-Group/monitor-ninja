<?php defined('SYSPATH') OR die('No direct access allowed.');
$prod_name = Kohana::config('config.product_name');

header('Content-Type: text/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8" ?>'; echo "\n"; ?>
<!DOCTYPE GraphXML SYSTEM "<?php echo $dtd; ?>">
<GraphXML xmlns:xlink="http://www.w3.org/1999/xlink">
<graph>

<style>
	<line tag="node" class="main" colour="#ffffff"/>
	<line tag="node" class="child_node" colour="blue"/>
	<line tag="node" class="nagios_node" colour="red"/>
	<line tag="node" class="relation_node" colour="green"/>
	<line tag="node" class="hs0" colour="#33FF00"/>
	<line tag="node" class="hs1" colour="#F83838"/>
	<line tag="node" class="hs2" colour="#F83838"/>
	<line tag="edge" class="secondary_edge" colour="blue" linestyle="dashed"/>
</style>
<node class="nagios_node" name="0">
	<label><?php echo sprintf($this->translate->_('%s Process'), $prod_name) ?></label>
</node>
<?php
foreach ($result as $node) {
	#"Host: %s Status: %s (Click for detail)", temp_host->name, host_status_texts[host_state]);
	$title_str = sprintf($this->translate->_('Host: %s Status: %s (Click for detail)'), $node->host_name, Current_status_Model::status_text($node->current_state));
	echo sprintf("<node class=\"hs%u\" name=\"%s\"><label>%s</label><dataref><ref xlink:show=\"replace\" xlink:href=\"status/host/%s\" xlink:title=\"%s\"/></dataref></node>\n",
		       $node->current_state, $node->host_name, $node->host_name, urlencode($node->host_name), $title_str);
}

if (is_array($data)) {
	foreach ($data as $host => $parent) {
		echo '<edge source="'.$parent.'" target="'.$host.'"/>';
	}
}

if (!empty($no_parents)) {
	foreach ($no_parents as $host) {
		echo '<edge source="0" target="'.$host.'"/>';
	}
}

echo '</graph></GraphXML>';
