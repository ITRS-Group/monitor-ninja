<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"></div>
<div id="dashing-dashboard-ninja"></div>
<script>
	$().ready(function () {
		var dashboard = $('#dashing-dashboard-ninja').dashinq(),
			index = 0;
		<?php foreach ($dashinq_widgets as $widget) { ?>

				var external_settings = <?php echo $widget['settings']; ?>,		// These are the widget display settings
					external_options = <?php echo $widget['options']; ?>;		// These are the options as declared in widget models

				var settings = {
					name: "<?php echo $widget['friendly_name']; ?>",
					name_id: "<?php echo $widget['id']; ?>",
					source: _site_domain + _index_page + "/widget/widget/<?php echo $widget['name']; ?>/index/",
					widget: "<?php echo $widget['name']; ?>",
					width: 300,
					height: 300,
					top: 100 + (index * 24),
					left: 100 + (index * 24)
				}

				
				if (external_settings) {
					for (var prop in external_settings) {
						if (prop == 'refresh_interval') {
							settings['refresh'] = parseInt(external_settings[prop]);
						} else {
							settings[prop] = external_settings[prop];
						}
					}
				}

				dashboard.widget(
					settings,
					external_options
				);

				index++;

		<?php } ?>
	});
</script>
<?php
