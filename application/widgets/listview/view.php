<?php defined('SYSPATH') OR die('No direct access allowed.');
$id = uniqid('listview_'); /* ID of span for this widget */
?>
<div id="<?php echo $id ?>">Loading...</div>
<div><a href="<?php echo url::base(true).'listview?q='.urlencode($this->args['query']); ?>">View in listview</a></div>
<script type="text/javascript">

$(function(){
	new lsfilter_list({
		table: $(<?php echo json_encode('#'.$id); ?>),
		per_page: <?php echo json_encode(intval($this->args['limit'])); ?>,
		request_url: _site_domain + _index_page + "/listview/fetch_ajax"
	}).update(<?php echo json_encode($this->args['query']); ?>);
});
</script>
