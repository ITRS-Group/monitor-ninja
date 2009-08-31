<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>">
	<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox" style="background-color: #ffffff; padding: 15px; float: right; margin-top: -1px; border: 1px solid #e9e9e0; right: 0px; width: 200px">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<fieldset>
		<label for="<?php echo $widget_id ?>_refresh"><?php echo $this->translate->_('Refresh (sec)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="<?php echo $widget_id ?>_refresh" id="<?php echo $widget_id ?>_refresh" value="<?php echo $refresh_rate ?>" />
		<div id="<?php echo $widget_id ?>_slider" style="z-index:1000"></div>
		</fieldset>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
	<?php } ?>
		<object	type="application/x-shockwave-flash" data="/nagvis/netmap/shell.swf"
			id="geomap" width="100%" height="400">
			<param name="base" value="/nagvis/netmap" />
			<param name="movie" value="/nagvis/netmap/shell.swf" />
			<param name="bgcolor" value="#ffffff" />
			<param name="wmode" value="opaque" />
			<param name="allowScriptAccess" value="sameDomain" />
		</object>
		<script type="text/javascript">
			if (!(document.attachEvent))
			{
				window.document["geomap"].addEventListener("DOMMouseScroll", handleWheel, false);
			}

			function handleWheel(event)
			{
				var app = window.document["geomap"];
				if (app)
				{
					var o = {x: event.clientX - parseInt($("#geomap").offset().left),
						y: event.clientY - parseInt($("#geomap").offset().top),
						delta: event.detail,
						ctrlKey: event.ctrlKey, altKey: event.altKey,
						shiftKey: event.shiftKey
					};

					app.handleWheel(o);
					event.preventDefault();
				}
			}
		</script>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<div style="clear:both"></div>
<?php } ?>