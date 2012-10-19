<?php

	$all_host_status_types = nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE;
	$all_service_status_types = nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_PENDING;
	$host_props = nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED;
	$service_props = nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED;

	/** Shortcut format 
	*
	*	href, icon (in x16 sheet), attributes
	*		string, string, array
	*
	* @param href is an adress, if external; use the full adress from protocol and up,
	*		if internal; give the relative adress, e.g. /tac, /status/service/all etc.
	*
	* @param icon is the name and only the name of the icon not the extension and not the path.
	*		must be in the x16 spritesheet
	*
	*	@param attribute An assoc. array containing additional attributes for the anchor, the class
	*		will always be image-link and nothing else.
	*
	*/

	$shortcuts = array('internal' => array(), 'external' => array());

	$shortcuts['internal'][] = array('#', 'refresh', array('title' => 'Refresh', 'onclick' => 'window.location.reload()'));
	$shortcuts['internal'][] = array('#', 'settings', array('title' => 'Settings', 'id' => 'settings_icon'));
	$shortcuts['internal'][] = array('/status/service/all?servicestatustypes='.($all_service_status_types).'&hostprops='.($host_props).'&service_props='.($service_props).'&hoststatustypes='.$all_host_status_types, 'shield-not-warning', array('title' => 'Unhandled Problems'));
	$shortcuts['internal'][] = array('/tac', 'hoststatus', array('title' => 'Tactical Overview'));

	if (Kohana::config('config.site_domain') === '/ninja/') {
		$shortcuts['external'][] = array('https://'.$_SERVER['HTTP_HOST'].'/ninja/dojo/index.html', 'edit', array('title' => 'DOJO Dev. Manual', 'target' => '_blank'));
	}
	
	if (isset($int_shortcuts)) {
		for ($i = 0; $i < count($int_shortcuts); $i++) {
			$shortcuts['internal'][] = $int_shortcuts[$i];
		}
	}

	if (isset($ext_shortcuts)) {
		for ($i = 0; $i < count($ext_shortcuts); $i++) {
			$shortcuts['external'][] = $ext_shortcuts[$i];
		}	
	}

?>

<div class="header" id="header">
	<div class="supermenu">

		<div class="logo">
			<?php echo html::image('application/views/themes/default/icons/icon.png', array('style' => 'margin: 8px;')); ?>
		</div>

		<ul>

			<!-- Classes are used by javascript navigation -->

			<li class="supermenu-button" id="about-button" title="About">
				<span class="icon-32 x32-info"></span>
			</li>
			<li class="supermenu-button" id="monitoring-button" title="Monitoring">
				<span class="icon-32 x32-monitoring"></span>
			</li>
			<li class="supermenu-button" id="reporting-button" title="Reporting">
				<span class="icon-32 x32-reporting"></span>
			</li>
			<li class="supermenu-button" id="configuration-button" title="Configuration">
				<span class="icon-32 x32-settings"></span>
			</li>

		</ul>
	</div>

	<div class="headercontent">
		

			<?php
				foreach ($shortcuts as $category => $buttons) {

					echo '<ul>';

					for($i = 0; $i < count($buttons); $i++) {

						$attributes = $buttons[$i][2];
						$attributes['class'] = 'image-link';

						echo '<li>'.html::anchor($buttons[$i][0], '<span class="icon-16 x16-'.$buttons[$i][1].'"></span>', $attributes).'</li>';

					}

					echo '</ul>';

				}
			?>

	</div>

	<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/lookup" id="global_search" method="get">
		<?php echo _('Welcome'); ?> <?php echo user::session('username') ?> | <?php echo html::anchor('default/logout', html::specialchars(_('Log out'))) ?><br />
		<?php
		$query = arr::search($_REQUEST, 'query');
		if ($query !== false && Router::$controller == 'search' && Router::$method == 'lookup') { ?>
			<input type="text" name="query" id="query" class="textbox" value="<?php echo $query ?>" />
		<?php } else { ?>
			<input type="text" name="query" id="query" class="textbox" value="<?php echo _('Search')?>" onfocus="this.value=''" onblur="this.value='<?php echo _('Search')?>'" />
		<?php	} ?>
		<?php try { echo help::render('search_help', 'search'); } catch (Zend_Exception $ex) {} ?>
	</form>

</div>

<div id="page_settings" class="page_settings">
	<ul>
		<li id="menu_global_settings" <?php	if (isset($disable_refresh) && $disable_refresh !== false) { ?> style="display:none"<?php } ?>><?php echo _('Global Settings') ?></li>
		<li id="noheader_ctrl" style="display:none">
			<input type="checkbox" id="noheader_chbx" value="1" /><label id="noheader_label" for="noheader_chbx"> <?php echo _('Hide page header')?></label>
		</li>
	<?php	if (!isset($disable_refresh) || $disable_refresh === false) { ?>
		<li id="ninja_page_refresh">
			<input type="checkbox" id="ninja_refresh_control" />
			<label id="ninja_refresh_lable" for="ninja_refresh_control"> <?php echo _('Pause refresh') ?></label>
		</li>
		<li id="ninja_refresh_edit">
			<?php echo _('Edit global refresh rate') ?><br />
			<div id="ninja_page_refresh_slider" style="width: 160px; margin-top: 8px;">
				<input type="text" maxlength="3" size="3" id="ninja_page_refresh_value" name="ninja_page_refresh_value" style="position: absolute; font-size: 11px; margin-left: 130px; padding: 1px; margin-top:-25px;z-index: 500" /> <div style="position: absolute; margin-left: 192px; margin-top: -23px"></div>
			</div>
		</li>

		<?php
			} # end if disable_refresh

			if (isset($widgets) && is_array($widgets)) {
				echo '<li>'._('Available Widgets').'</li>'."\n";
				foreach($widgets as $widget) {
					$class_name = isset($widget->id) ? 'selected' : 'unselected';
					echo '<li id="li-'.$widget->name.'-'.$widget->instance_id.'" data-name="'.$widget->name.'" data-instance_id="'.$widget->instance_id.'" class="'.$class_name.' widget-selector" onclick="control_widgets(this)">'.$widget->friendly_name.'</li>'."\n";
				}
				echo '<li onclick="restore_widgets();">'._('Restore overview to factory settings').'</li>'."\n";
				if ($authorized === true) {
					echo '<li onclick="widget_upload();">'._('Upload new widget').'</li>'."\n";
				}
				echo '<li id="show_global_widget_refresh">'._('Set widget refresh rate (s.)').'</li>'."\n";
			}
		?>
	</ul>
	
</div>