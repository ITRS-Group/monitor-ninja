<?php

	/** Shortcut format 
	*
	*	href, icon (in x16 sheet), attributes
	*		string, string, array
	*
	* @param href is an adress, if external; use the full adress from protocol and up,
	*		if internal; give the relative adress, e.g. /tac, /status/service/all etc.
	*
	* @param icon is the class of the spritesheet icon
	*
	*	@param attribute An assoc. array containing additional attributes for the anchor, the class
	*		will always be image-link and nothing else.
	*
	*/

	$show_settings = ((isset($widgets) && is_array($widgets)) || (!isset($disable_refresh) || $disable_refresh === false) || (isset($listview_refresh) && $listview_refresh === true));

	$shortcuts = array('internal' => array(), 'external' => array());

	$shortcuts['internal'][] = array('#', 'icon-16 x16-refresh', array('title' => 'Refresh', 'onclick' => 'window.location.reload()'));

	if ($show_settings) {
		$shortcuts['internal'][] = array('#', 'icon-16 x16-settings', array('title' => 'Settings', 'id' => 'page_settings_icon'));
	}

	if (isset($global_notifications) && is_array($global_notifications) && count($global_notifications) >= 1) {
		$shortcuts['internal'][] = array('#', 'icon-16 x16-notifications', array('title' => 'Global Notifications', 'id' => 'global_notifications_icon'));
	}
	$shortcuts['internal'][] = array('/listview?q=[services] (state != 0 and acknowledged = 0) or (host.state != 0 and host.acknowledged = 0)', 'icon-16 x16-shield-pending', array('id' => 'uh_problems', 'title' => 'Unhandled Problems'));
	$shortcuts['internal'][] = array('/tac', 'icon-16 x16-hoststatus', array('title' => 'Tactical Overview'));
	
	if( isset($help_link) && $help_link !== false ) {
		$shortcuts['internal'][] = array($help_link, 'icon-16 x16-help', array('title' => 'Documentation', 'id' => 'help_icon'));
	}

	if (isset($int_shortcuts)) {
		for ($i = 0; $i < count($int_shortcuts); $i++) {
			$shortcuts['internal'][] = $int_shortcuts[$i];
		}
	}
?>

<div class="header" id="header">
	<div class="supermenu">

		<div class="logo">
			<div class="logo-image"></div>
		</div>

		<ul>
			<!-- Classes are used by javascript navigation -->

		<?php
			if(isset($links)) {
				foreach($links as $section => $sections_links) {
					if(empty($sections_links) && strtolower($section) != "about") {
						// we want to whitelist the 'about' link since the ninja/nagios version is displayed there
						continue;
					} ?>
				<li class="supermenu-button" id="<?php echo str_replace(' ','-',strtolower($section)); ?>-button" title="<?php echo $section; ?>">
					<span class="icon-32 x32-<?php echo str_replace(' ','-',strtolower($section)); ?>"></span>
				</li>
		<?php
				}
			}
		?>
		</ul>
	</div>

	<div class="headercontent">

			<?php
				$quri = '/'.url::current();

				

					$buttons = $shortcuts['internal'];

					echo '<ul id="dojo-quicklink-internal">';

					for($i = 0; $i < count($buttons); $i++) {

						$attributes = $buttons[$i][2];
						$attributes['class'] = 'image-link';
						$stripped = explode('?', $buttons[$i][0]);
						$stripped = $stripped[0];

						if ($quri == $stripped)
							echo '<li class="selected">'.html::anchor($buttons[$i][0], '<span class="icon-16 x16-'.$buttons[$i][1].'"></span>', $attributes).'</li>';
						else
							echo '<li>'.html::anchor($buttons[$i][0], '<span class="'.$buttons[$i][1].'"></span>', $attributes).'</li>';
					}

					echo '</ul>';

			?>
	</div>
	<div class="headercontent" style="margin-left: 8px;">
		<ul id="dojo-quicklink-external">
		</ul>
	</div>
	<a href="#dojo-add-quicklink-menu" title="Manage quickbar" class="icon-12 x12-box-config no_border" id="dojo-add-quicklink" style="opacity: 0.5; margin-top: 22px; display: inline-block;"></a>
	<div style="display: none">
		<div id="dojo-add-quicklink-menu">
			<form action="">
				<h1>Add new quicklink</h1>
				<hr />
				<table class="no_border">
					<tr>
						<td><?php echo _('URI') ?>:</td>
						<td><input type="text" id="dojo-add-quicklink-href"></td>
					</tr>
					<tr>
						<td><?php echo _('Title') ?>:</td>
						<td><input type="text" id="dojo-add-quicklink-title"></td>
					</tr>
					<tr>
						<td><?php echo _('Open in') ?>:</td>
						<td>
							<select id="dojo-add-quicklink-target">
								<option value=""><?php echo _('This window') ?></option>
								<option value="_BLANK"><?php echo _('New window') ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo _('Icon') ?>:</td>
						<td>
							<input type='hidden' id='dojo-add-quicklink-icon' name='dojo-add-quicklink-icon' />
							<table style="width: auto" id="dojo-icon-container">
								<tr>
							<?php
								$icons = glob((__DIR__) . '/icons/x16/*.png');
								$counter = 0;
								foreach ($icons as $icon) {
									$name = pathinfo($icon, PATHINFO_FILENAME);
									echo "<td><span data-icon='$name' class='icon-16 x16-$name'></span></td>";
									if(++$counter % 16 === 0) {
										$counter = 0;
										echo "</tr><tr>";
									}
								}
							?>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><?php echo _('Remove selected quicklinks') ?>:</td>
						<td>
							<ul id="dojo-quicklink-remove"></ul>
					</td>
					</tr>
					<tr>
						<td colspan=2>
							<input type="submit" value="<?php echo _('Save') ?>" />
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
	<?php
	if(Auth::instance()->logged_in()) {
		$timezone = date_default_timezone_get();
?>
		<div style="position: fixed; top: 6px; left: 337px; font-size: 90%; color: #555;">
			<?php
				if (isset($_SERVER['SERVER_NAME']))
					echo _('Host').': ' . htmlentities($_SERVER['SERVER_NAME']) . ' &nbsp; ';
			?>
			<?php echo _('Updated') ?>: <a id="page_last_updated" data-utc_offset="<?php echo (1000 * date::utc_offset($timezone)) ?>" title="Your timezone is set to <?php echo $timezone ?>. Click to reload page." href="<?php echo isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "#" /* For CLI */ ?>"><?php echo date(nagstat::date_format()) ?></a>
		</div>
	<?php } ?>

	<div class="header_right">
	<div class="global_search">
	<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/lookup" method="get">
		<?php echo _('Welcome'); ?> <?php echo html::anchor('user', strlen(user::session('realname')) > 0 ? user::session('realname') : user::session('username')) ?> | <?php echo html::anchor('default/logout', html::specialchars(_('Log out'))) ?><br />
		<span id="my_saved_searches" style="padding: 4px; vertical-align: text-bottom; cursor: pointer;"><img id="my_saved_searches_img" title="Click to view your saved searches" src="/monitor/application/views/icons/16x16/save_search.png" /></span>
		<?php
		$query = arr::search($_REQUEST, 'query');
		if ($query !== false && Router::$controller == 'search' && Router::$method == 'lookup') { ?>
			<input type="text" name="query" id="query" class="textbox" value="<?php echo htmlentities($query, ENT_COMPAT, 'UTF-8') ?>" />
		<?php } else { ?>
			<input type="text" name="query" id="query" class="textbox" value="<?php echo _('Search')?>" onfocus="this.value=''" onblur="this.value='<?php echo _('Search')?>'" />
		<?php	} ?>
		<?php try { echo help::render('search_help', 'search'); } catch (Zend_Exception $ex) {} ?>
	</form>
	</div>
	<?php customlogo::Render(); ?>
	</div>
</div>

<?php
	if (isset($global_notifications) && is_array($global_notifications) && count($global_notifications) >= 1) {
		echo "<div id='global_notifications'><ul>";

		foreach ($global_notifications as $gn) {
			echo "<li>";
			echo (!$gn[1]) ? '<span class="icon-12 x12-shield-warning" style="vertical-align: middle;"></span>': '';
			echo $gn[0]."</li>";
		}
		echo "</ul><div class='clear'></div></div>";
	}
?>

<?php
	if ($show_settings) {
?>
<div id="page_settings" class="page_settings">
	<ul>
		<li id="menu_global_settings" <?php	if ((isset($disable_refresh) && $disable_refresh !== false) && !isset($listview_refresh)) { ?> style="display:none"<?php } ?>><?php echo _('Global Settings') ?></li>
		<li id="noheader_ctrl" style="display:none">
			<input type="checkbox" id="noheader_chbx" value="1" /><label id="noheader_label" for="noheader_chbx"> <?php echo _('Hide page header')?></label>
		</li>
	<?php	if (!isset($disable_refresh) || $disable_refresh === false) { ?>
		<li id="ninja_page_refresh">
			<input type="checkbox" id="ninja_refresh_control" />
			<label id="ninja_refresh_lable" for="ninja_refresh_control"> <?php echo _('Pause page refresh') ?></label>
		</li>
		<li id="ninja_refresh_edit">
			<?php echo _('Edit global refresh rate') ?><br />
			<div id="ninja_page_refresh_slider" style="width: 160px; margin-top: 8px;">
				<input type="text" maxlength="3" size="3" id="ninja_page_refresh_value" name="ninja_page_refresh_value" data-key="config.page_refresh_rate" style="position: absolute; font-size: 11px; margin-left: 130px; padding: 1px; margin-top:-25px;z-index: 500" />
				<div style="position: absolute; margin-left: 192px; margin-top: -23px"></div>
			</div>
		</li>

		<?php
			} # end if disable_refresh
	if (isset($listview_refresh) && $listview_refresh === true) { ?>
		<li id="listview_refresh">
			<input type="checkbox" id="listview_refresh_control" />
			<label id="listview_refresh_lable" for="listview_refresh_control"> <?php echo _('Pause list view refresh') ?></label>
		</li>
		<li id="listview_refresh_edit">
			<?php echo _('Edit listview refresh rate') ?><br />
			<div id="listview_refresh_slider" style="width: 160px; margin-top: 8px;">
				<input type="text" maxlength="3" size="3" id="listview_refresh_value" name="listview_refresh_value" data-key="config.listview_refresh_rate" style="position: absolute; font-size: 11px; margin-left: 130px; padding: 1px; margin-top:-25px;z-index: 500" />
				<div style="position: absolute; margin-left: 192px; margin-top: -23px"></div>
			</div>
		</li>
		<?php
			} # end if listview_refresh
			if (isset($widgets) && is_array($widgets)) {
				echo '<li><h2>'._('Available Widgets').'</h2></li>'."\n";
				foreach($widgets as $widget) {
					$class_name = isset($widget->id) ? 'selected' : 'unselected';
					echo '<li id="li-'.$widget->name.'-'.$widget->instance_id.'" data-name="'.$widget->name.'" data-instance_id="'.$widget->instance_id.'" class="'.$class_name.' widget-selector" onclick="control_widgets(this)">'.$widget->friendly_name.'</li>'."\n";
				}
				echo "<li><h2>"._('Widget settings')."</h2></li>";
				echo '<li><form action="'.url::base(true).'widget/factory_reset_widgets" method="post"><input type="submit" class="plain" value="'._("Restore overview to factory settings").'" /></form></li>'."\n";
				if ($authorized === true) {
					echo '<li><a href="'.url::base(true).'upload">'._('Upload new widget').'</a></li>'."\n";
				}
				echo '<li><a href="#" id="show_global_widget_refresh">'._("Set every widget's refresh rate to (s.)").'</a></li>'."\n";
			}
		?>
	</ul>
	
</div>

<?php
	}
?>
