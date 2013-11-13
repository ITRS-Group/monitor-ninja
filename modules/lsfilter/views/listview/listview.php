<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php echo form::open('command/multi_action', array('id'=>'listview_multi_action_form')); ?>
<div class="clear" id="filter_result"><div style="text-align: center; margin: 32px;"><span class="lsfilter-loader"><?php echo _('Loading...');?></span></div></div>
<input type="hidden" id="listview_multi_action_obj_action" name="multi_action" value="" />
<input type="hidden" id="listview_multi_action_obj_type" name="obj_type" value="" />
<?php echo form::close(); ?>

<div id="filter-query-multi-action">
	<h2><?php echo _('Multi Action'); ?></h2>
	<ul id="multi-action-list">
	<li>Table doesnt allow multi select</li>
	</ul>
	<div id="multi-action-message"></div>
</div>

<div id="filter-query-builder">

	<div style="margin: 8px 0 0 8px">
		<input type="text" id="lsfilter_save_filter_name" placeholder="<?php echo _('Filter Name');?>" />
		<button id="lsfilter_save_filter"><?php echo _('Save Filter');?></button>
		<?php if(op5auth::instance()->authorized_for('saved_filters_global')) { ?>
		<input type="checkbox" id="lsfilter_save_filter_global" /> <?php echo _('Make global'); ?>
		<?php } ?>
	</div>

	<h2><?php echo _('Manual input'); ?></h2>

	<form action="#" onsubmit="dosubmit();">
		<textarea style="width: 98%; height: 30px" name="filter_query"
			id="filter_query"><?php echo htmlentities($query, ENT_COMPAT, 'UTF-8'); ?></textarea>
		<input type="hidden" name="filter_query_order"
			id="filter_query_order" value="<?php echo htmlentities($query_order, ENT_COMPAT, 'UTF-8'); ?>" />
	</form>
	<div id="filter-query-status"></div>

	<h2><?php echo _('Graphical input'); ?></h2>

	<form id="filter_visual_form">
		<div id="filter_visual">Filter</div>
	</form>
</div>
