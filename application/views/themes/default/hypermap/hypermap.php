<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!isset($hyperapplet_path)) {
	echo $this->translate->_('Hyperapplet does not seem to be correctly set up.');
	die();
}
?>

<table border=1 cellpadding=0 cellspacing=0 class='linkBox'>
	<tr>
		<td class='linkBox'>
			<?php echo html::anchor('status/host/all', $this->translate->_('View Status Detail For All Hosts')) ?><br />
			<?php echo html::anchor('status/hostgroup?items_per_page='.config::get('pagination.group_items_per_page', '*'), $this->translate->_('View Status Overview For All Hosts')) ?>
		</td>
	</tr>
</table>

<a name="graph-section"></a>
<applet code="hypergraph.applications.hexplorer.HExplorerApplet.class" archive="<?php echo $hyperapplet_path ?>" width="100%" height="100%" align="baseline">
<param name="file" value="<?php echo $xml_path ?>">
<param name="properties" value="<?php echo $nagios_prop ?>">
</param>
</applet>