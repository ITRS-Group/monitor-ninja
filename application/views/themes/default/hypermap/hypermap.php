<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!isset($hyperapplet_path)) {
	echo $this->translate->_('Hyperapplet does not seem to be correctly set up.');
	die();
}
?>

<div class="widget left w32" id="page_links">
	<ul>
		<li>View, for all hosts:</li>
		<li><?php echo html::anchor('status/host/all', $this->translate->_('Status Detail')) ?></li>
		<li><?php echo html::anchor('status/hostgroup?items_per_page='.config::get('pagination.group_items_per_page', '*'), $this->translate->_('Status Overview')) ?></li>
	</ul>
</div>

<a name="graph-section"></a>
<?php if (isset($hyperapplet_path)) { ?>
<applet id="hypermap" code="hypergraph.applications.hexplorer.HExplorerApplet.class" archive="<?php echo $hyperapplet_path ?>" width="100%" height="100%" align="baseline" mayscript="true">
<param name="file" value="<?php echo $xml_path ?>">
<param name="properties" value="<?php echo $nagios_prop ?>">
</applet>
<?php } else {?>
<p>Couldn't find the hypergraph applet</p>
<?php } ?>
