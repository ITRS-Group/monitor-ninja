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

	$shortcuts['internal'][] = array('#', 'icon-16 x16-refresh', array('title' => 'Refresh', 'onclick' => 'window.location.reload(); return false;'));

	if ($show_settings) {
		$shortcuts['internal'][] = array('#', 'icon-16 x16-settings', array('title' => 'Settings', 'id' => 'page_settings_icon'));
	}

	$shortcuts['internal'][] = array('/listview?q=[hosts] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0', 'icon-16 x16-shield-pending', array('id' => 'uh_host_problems', 'title' => 'Unhandled Host Problems'));
	$shortcuts['internal'][] = array('/listview?q=[services] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0 and host.scheduled_downtime_depth = 0', 'icon-16 x16-shield-pending', array('id' => 'uh_service_problems', 'title' => 'Unhandled Service Problems'));
	$shortcuts['internal'][] = array('/tac', 'icon-menu menu-tac', array('title' => 'Tactical Overview'));

	if (isset($int_shortcuts)) {
		for ($i = 0; $i < count($int_shortcuts); $i++) {
			$shortcuts['internal'][] = $int_shortcuts[$i];
		}
	}
?>

<div class="header" id="header">

	<?php
		require __DIR__.'/template_menu.php';
	?>

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
	<a href="#dojo-add-quicklink-menu" title="Manage quickbar" class="icon-16 x16-link no_border" id="dojo-add-quicklink"></a>
	<div style="display: none">
		<div id="dojo-add-quicklink-menu">
			<form action="">
				<h1>Add new quicklink</h1>
				<hr />
				<table class="no_border">
					<tr>
						<td><label for="dojo-add-quicklink-href"><?php echo _('URI') ?>:</label></td>
						<td><input type="text" id="dojo-add-quicklink-href"></td>
					</tr>
					<tr>
						<td><label for="dojo-add-quicklink-title"><?php echo _('Title') ?>:</label></td>
						<td><input type="text" id="dojo-add-quicklink-title"></td>
					</tr>
					<tr>
						<td><label for="dojo-add-quicklink-target"><?php echo _('Open in') ?>:</label></td>
						<td>
							<select id="dojo-add-quicklink-target">
								<option value=""><?php echo _('This window') ?></option>
								<option value="_BLANK"><?php echo _('New window') ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="dojo-add-quicklink-icon"><?php echo _('Icon') ?>:</label></td>
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

	<div class="header_right">
		<div class="global_search">
			<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/lookup" method="get">
				<?php
					if ( Auth::instance()->logged_in() ) {
						echo html::anchor('user', html::specialchars(strlen(user::session('realname')) > 0 ? user::session('realname') : user::session('username')));
						echo " at " . html::specialchars(gethostname());
						if ( !op5auth::instance()->authorized_for('no_logout') ) {
							echo " | " . html::anchor(Kohana::config('routes.log_out_action'), html::specialchars(_('Log out')));
						}
					}
				?>

				<br />
				<?php
				$query = arr::search($_REQUEST, 'query');
				if ($query !== false && Router::$controller == 'search' && Router::$method == 'lookup') { ?>
					<input type="text" name="query" id="query" class="textbox" value="<?php echo html::specialchars($query) ?>" />
				<?php } else { ?>
					<input type="text" name="query" id="query" class="textbox" value="<?php echo _('Search')?>" onfocus="this.value=''" onblur="this.value='<?php echo _('Search')?>'" />
				<?php	} ?>
				<?php echo help::render('search_help', 'search'); ?>
			</form>
		</div>
		<?php customlogo::Render(); ?>
	</div>

	<div class="clear"></div>

	<?php
		if ( isset( $toolbar ) && get_class( $toolbar ) == "Toolbar_Controller" ) {
			$toolbar->render();
		}
	?>

</div>

<?php
	if ($show_settings) {
?>
<div id="page_settings" class="page_settings">
	<ul>
		<li id="menu_global_settings" <?php	if ((isset($disable_refresh) && $disable_refresh !== false) && !isset($listview_refresh)) { ?> style="display:none"<?php } ?>><?php echo _('Global Settings') ?></li>
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
			<label for="listview_refresh_value"><?php echo _('Edit listview refresh rate') ?></label> <input type="text" maxlength="2" size="3" id="listview_refresh_value" name="listview_refresh_value" data-key="config.listview_refresh_rate" /><br />
			<div id="listview_refresh_slider">
				<div></div>
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
				echo '<li><a href="#" id="show_global_widget_refresh">'._("Set every widget's refresh rate to (s.)").'</a></li>'."\n";
			}
		?>
	</ul>

</div>

<?php
	}
?>
