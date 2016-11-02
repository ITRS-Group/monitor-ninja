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

	if ($show_settings) {
		$shortcuts['internal'][] = array('#', 'icon-16 x16-settings', array('title' => 'Settings', 'id' => 'page_settings_icon'));
	}

	$shortcuts['internal'][] = array('/listview?q=[hosts] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0', 'icon-16 x16-shield-pending', array('id' => 'uh_host_problems', 'title' => 'Unhandled Host Problems'));
	$shortcuts['internal'][] = array('/listview?q=[services] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0 and host.scheduled_downtime_depth = 0', 'icon-16 x16-shield-pending', array('id' => 'uh_service_problems', 'title' => 'Unhandled Service Problems'));

	if (isset($int_shortcuts)) {
		for ($i = 0; $i < count($int_shortcuts); $i++) {
			$shortcuts['internal'][] = $int_shortcuts[$i];
		}
	}
?>

<div class="header" id="header">

	<?php
	if (isset($menu)) {
		$menu_widget = new View('menu', array(
			'menu' => $menu,
			'orientation' => 'left',
			'class' => 'main-menu'
		));
		echo $menu_widget->render();
	}
	?>

	<div class="headercontent">

			<?php
				$quri = '/'.url::current();

					$buttons = $shortcuts['internal'];

					echo '<ul id="quicklinks" class="quicklinks">';

					for($i = 0; $i < count($buttons); $i++) {

						$attributes = $buttons[$i][2];
						$attributes['class'] = 'image-link';
						$stripped = explode('?', $buttons[$i][0]);
						$stripped = $stripped[0];

						if ($quri == $stripped)
							echo '<li class="selected">'.html::anchor($buttons[$i][0], '<span class="icon-16 x16-'.$buttons[$i][1].'"></span><span class="quicklink-badge"></span>', $attributes).'</li>';
						else
							echo '<li>'.html::anchor($buttons[$i][0], '<span class="'.$buttons[$i][1].'"></span><span class="quicklink-badge"></span>', $attributes).'</li>';
					}

					$link = LinkProvider::factory()->get_url("quicklink", "index");
					echo '<li><a id="dojo-add-quicklink" href="'.$link.'" title="Quicklinks" class="image-link"><span class="icon-16 x16-link"></span></a></li>';
					echo '</ul>';

			?>
	</div>

	<?php

	if (PHP_SAPI !== 'cli') {
		$query = arr::search($_REQUEST, 'query');
		echo View::factory('finder', array(
			'query' => arr::search($_REQUEST, 'query')
		))->render();

		if (Auth::instance()->logged_in()) {
			echo View::factory('profile', array(
				'avatar' => Auth::instance()->get_user()->get_avatar_url(),
				'user' => Auth::instance()->get_user(),
				'host' => gethostname()
			))->render();
		}
	}

	?>

	<?php customlogo::Render(); ?>
	<div class="clear"></div>

	<?php
		if (isset($toolbar) && get_class($toolbar) == "Toolbar_Controller") {
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
			<label for="listview_refresh_value"><?php echo _('Edit listview refresh rate') ?></label> <input type="text" maxlength="3" size="3" id="listview_refresh_value" name="listview_refresh_value" data-key="config.listview_refresh_rate" /><br />
			<div id="listview_refresh_slider">
				<div></div>
			</div>
		</li>
		<?php
			} # end if listview_refresh
		?>
	</ul>

</div>

<?php
	}
?>
