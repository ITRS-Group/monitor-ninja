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

	$show_settings = ((isset($widgets) && is_array($widgets)) || (isset($listview_refresh) && $listview_refresh === true));

	if ($show_settings) {
		array_unshift($quicklinks['internal'],
			new Quicklink_Model('settings', '#', array('title' => 'Settings', 'id' => 'page_settings_icon')));
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
				echo '<ul id="quicklinks" class="quicklinks">';
				foreach ($quicklinks['internal'] as $quicklink) {
					echo $quicklink->get_html();
				}
				echo '<li><a id="dojo-add-quicklink" href="#dojo-add-quicklink-menu" title="Manage quickbar" class="image-link"><span class="icon-16 x16-link"></span></a></li>';
				echo '</ul>';

			?>
	</div>
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
		<li id="menu_global_settings" <?php	if (!isset($listview_refresh)) { ?> style="display:none"<?php } ?>><?php echo _('Global Settings') ?></li>
		<?php
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
