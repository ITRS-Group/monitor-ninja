<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php if (!$ajax_call) { ?>
<div class="widget editable movable collapsable removable closeconfirm" id="widget-<?php echo $widget_id ?>" style="position: relative;">
	<div class="widget-header"><span class="<?php echo $widget_id ?>_editable" id="<?php echo $widget_id ?>_title"><?php echo $title ?></span></div>
	<div class="widget-editbox" style="position: absolute;">
		<?php echo form::open('ajax/save_widget_setting', array('id' => $widget_id.'_form', 'onsubmit' => 'return false;')); ?>
		<fieldset>
		<div id="<?php echo $widget_id ?>_slider" style="display: none;"></div>
		<label for="<?php echo $widget_id ?>_map"><?php echo $this->translate->_('Map') ?>:</label>
		<?php echo form::dropdown(array('name' => $widget_id . '_map'), $all_maps, $choosen_map); ?>
		<br />
		<label for="<?php echo $widget_id ?>_height"><?php echo $this->translate->_('Height (px)') ?>:</label>
		<input style="border:0px solid red; display: inline; padding: 0px; margin-bottom: 7px;" size="3" type="text" name="<?php echo $widget_id ?>_height" id="<?php echo $widget_id; ?>_height" value="<?php echo floatval($height) ?>" />
		</fieldset>
		<?php echo form::close() ?>
	</div>
	<div class="widget-content">
	<?php } ?>
	<?php if ($choosen_map == 'geomap') { ?>
	<object	type="application/x-shockwave-flash" data="/nagvis/netmap/shell.swf"
			id="nagvis" width="100%" <?php if ($height > 0) echo 'style="height: '.$height.'px;"'?>>
		<param name="base" value="/nagvis/netmap" />
		<param name="movie" value="/nagvis/netmap/shell.swf" />
		<param name="bgcolor" value="#ffffff" />
		<param name="wmode" value="opaque" />
		<param name="allowScriptAccess" value="sameDomain" />
	</object>

	<script type="text/javascript">
	if (!(document.attachEvent))
		window.document["nagvis"].addEventListener("DOMMouseScroll", handleWheel, false);
	else
		window.document["nagvis"].onmousewheel = handleWheel;

	function handleWheel(event)
	{
		var app = window.document["nagvis"];
		if (app)
		{
			if (!event)
				event = window.event;

			var o = {x: event.clientX - parseInt($("#nagvis").offset().left),
				y: event.clientY - parseInt($("#nagvis").offset().top),
				delta: (event.detail ? event.detail : -event.wheelDelta/40),
				ctrlKey: event.ctrlKey, altKey: event.altKey,
				shiftKey: event.shiftKey
			};

			app.handleWheel(o);
			if (event.preventDefault)
				event.preventDefault();
			else
				event.returnValue = false;
		}
	}
	</script>
	<?php } else if ($choosen_map == 'automap') { ?>
	<iframe name="nagvis" id="nagvis" src="/nagvis/nagvis/index.php?automap=1" width="100%" <?php if ($height > 0) echo 'style="height: '.$height.'px;"'?>>
		Error : Can not load NagVis.
	</iframe>
	<?php } else { ?>
	<iframe name="nagvis" id="nagvis" src="/nagvis/nagvis/?map=<?php echo $choosen_map; ?>" width="100%" <?php if ($height > 0) echo 'style="height: '.$height.'px;"'?>>
		Error : Can not load NagVis.
	</iframe>
	<?php } ?>
<?php if (!$ajax_call) { ?>
	</div>
</div>
<div style="clear:both"></div>
<?php } ?>
