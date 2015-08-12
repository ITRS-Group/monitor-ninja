<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php echo form::open('cmd', array('id'=>'listview_multi_action_form')); ?>
<div class="clear" id="filter_result"><div style="text-align: center; margin: 32px;"><span class="lsfilter-loader"><?php echo _('Loading...');?></span></div></div>
<input type="hidden" id="listview_multi_action_obj_action" name="command" value="" />
<input type="hidden" id="listview_multi_action_obj_type" name="table" value="" />
<?php echo form::close(); ?>
<div id="extra-dropdowns"></div>
<div id="filter-query-builder" class="filter-query-dropdown">

	<div style="margin: 8px 0 0 8px">
		<input type="text" id="lsfilter_save_filter_name" placeholder="<?php echo _('Filter Name');?>" />
		<button id="lsfilter_save_filter"><?php echo _('Save Filter');?></button>
		<?php if(op5auth::instance()->authorized_for('saved_filters_global')) { ?>
		<label><input type="checkbox" id="lsfilter_save_filter_global" /> <?php echo _('Make global'); ?></label>
		<?php } ?>
	</div>

	<h2><?php echo _('Manual input'); ?></h2>

	<form action="#" onsubmit="dosubmit();">
		<textarea style="width: 98%; height: 30px" name="filter_query"
			id="filter_query"></textarea>
		<input type="hidden" name="filter_query_order"
			id="filter_query_order" value="" />
	</form>
	<div id="filter-query-status"></div>

	<h2><?php echo _('Graphical input'); ?></h2>

	<form id="filter_visual_form">
		<div id="filter_visual">Filter</div>
	</form>
	<button id="close-filter-builder">Close filter builder</button>
 </div>
