<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<script type="text/javascript">
function highlight(){
	if ('<?php echo $mark_object_type; ?>' != 'host')
		return;

	$('#nagvis').contents().find('area').each(function(){
		var url_parts = $(this).attr('href').split('/');
		if (url_parts.pop() == '<?php echo $mark_object_name; ?>')
		{
			var coords = $(this).attr('coords').split(',');
			if (coords.length == 4)
			{
				$('#nagvis').contents().find('#map').append('<div id="mark" style="position: absolute; left: '
					+ (coords[0] - 2) + 'px; top: ' + (coords[1] - 2) + 'px; width: ' + (coords[2] - coords[0])
					+ 'px; height: ' + (coords[3] - coords[1]) + 'px; border: 2px solid #3333FF;'
					+ ' -moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px;'
					+ ' background-color: transparent;"></div>');
				$('#nagvis').contents().find('#mark').mouseover($(this).attr('onmouseover'));
				$('#nagvis').contents().find('#mark').mouseout($(this).attr('onmouseout'));
				var target = $(this);
				$('#nagvis').contents().find('#mark').click(function(){
					$(target).click();
				});
				return false;
			}
			/* else - we don't handle this */
		}
	});
}
</script>
<div style="margin-left: 1px;">
	<iframe name="nagvis" id="nagvis" src="/nagvis/nagvis/index.php?automap=1" width="100%" onload="highlight();">
		Error : Can not load NagVis.
	</iframe>
</div>
