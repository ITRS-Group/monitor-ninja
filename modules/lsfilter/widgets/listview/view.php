<?php defined('SYSPATH') OR die('No direct access allowed.');
$id = uniqid('listview_'); /* ID of span for this widget */
?>
<div id="<?php echo $id ?>">Loading...</div>
<?php if($this->branding['listview_link']): ?>
<div style="padding: 0 0 1px 0"><a href="<?php echo url::base(true).'listview?q='.urlencode($this->args['query']); ?>">View in listview</a></div>
<?php endif; ?>
<script type="text/javascript">

$(function(){
	var list = new lsfilter_list({
		table: $(<?php echo json_encode('#'.$id); ?>),
		per_page: <?php echo json_encode(intval($this->args['limit'])); ?>,
		request_url: _site_domain + _index_page + "/listview/fetch_ajax",
		columns: <?php echo json_encode( $this->args['columns'] ); ?>
	});
	list.on.update_ok(<?php echo json_encode(array(
		'query' => $this->args['query'],
		'order' => $this->args['order']
	)); ?>);
});
</script>
