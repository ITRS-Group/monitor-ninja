<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php echo form::open('command/multi_action', array('id'=>'listview_multi_action_form')); ?>
<div class="clear" id="filter_result"><div style="text-align: center; margin: 32px;"><span class="lsfilter-loader"><?php echo _('Loading...');?></span></div></div>
<input type="hidden" id="listview_multi_action_obj_action" name="multi_action" value="" />
<input type="hidden" id="listview_multi_action_obj_type" name="obj_type" value="" />
<?php echo form::close(); ?>
<div id="extra-dropdowns"></div>
