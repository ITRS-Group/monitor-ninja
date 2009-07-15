<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php //if (extension_loaded('zlib')) { ob_start('ob_gzhandler'); } header('Content-type: text/html; charset=utf-8'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo (isset($title)) ? 'Ninja » '.ucwords(html::specialchars($title)) : 'Ninja' ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo str_replace('index.php/','',url::site('application/views/themes/default/css/default/common.css.php')) ?>" />
		<?php echo html::link('application/views/themes/default/icons/16x16/favicon.ico','icon','image/icon') ?>
		<!--[If IE]>
		<link type="text/css" rel="stylesheet" href="<?php echo str_replace('index.php/','',url::site('application/views/themes/default/css/default/ie7.css.php')) ?>" />
		<![endif]-->
		<?php
			echo (!empty($css_header)) ? $css_header : '';
			echo html::script('application/media/js/jquery.min.js');
			echo html::script('application/media/js/jquery-ui.min.js');
			echo html::script('application/media/js/jquery.form.js');
			echo html::script('application/media/js/jquery.easywidgets.min.js');
			echo html::script('application/media/js/jquery.autocomplete.min');
			echo html::script('application/media/js/jquery.selectboxes.min.js');
			echo html::script('application/media/js/jquery.jeditable.min');
			echo html::script('application/media/js/jquery.query.js');
			echo html::script('application/media/js/jquery.jgrowl.js');
			echo html::script('application/views/themes/default/js/collapse_menu.js');
			echo html::script('application/views/themes/default/js/global_search.js');
			echo html::script('application/views/themes/default/js/pagination.js');
			if (!isset($disable_refresh) || $disable_refresh === false) {
				refresh::control();
			}
		?>
		<script type="text/javascript">
			//<!--
				var _site_domain = '<?php echo Kohana::config('config.site_domain') ?>';
				var _index_page = '<?php echo Kohana::config('config.index_page') ?>';
				var _current_uri = '<?php echo Router::$controller.'/'.Router::$method ?>';
				var _widget_refresh_msg = '<?php echo $this->translate->_('Refresh rate for all widgets has been updated to %s sec'); ?>';
				var _widget_refresh_error = '<?php echo $this->translate->_('Unable to update refresh rate for all widgets.'); ?>';
				var _widget_global_refresh_error = '<?php echo $this->translate->_('An error was encountered when trying to update refresh rate for all widgets.'); ?>';
				var _widget_order_error = '<?php echo $this->translate->_('Unable to fetch widget order from database.'); ?>';
				var _widget_settings_msg = '<?php echo $this->translate->_('Settings for widget %s was updated'); ?>';
				var _widget_settings_error = '<?php echo $this->translate->_('Unable to fetch setting for widget %s'); ?>';
				var _widget_notfound_error = '<?php echo $this->translate->_('Unable to find widget %s'); ?>';
				var _page_refresh_msg = '<?php echo $this->translate->_('Updated page refresh rate to %s seconds'); ?>';
				var _success_header = '<?php echo $this->translate->_('Success'); ?>';
				var _error_header = '<?php echo $this->translate->_('ERROR'); ?>';
				<?php
				if (!empty($inline_js)) {
					echo "$(document).ready(function() {";
					echo $inline_js;
					echo "});";
				}
				?>
			//-->
		</script>
		<?php echo html::script('application/views/themes/default/js/common.js'); ?>
		<?php echo (!empty($js_header)) ? $js_header : ''; ?>
	</head>

	<body>
		<div id="top-bar">
			<?php echo html::image('application/views/themes/default/icons/ninja.png','NINJA'); ?>
			<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/lookup" id="global_search" method="get">
				<div id="navigation">
					<ul>
					<?php
					if (isset($title)){
						$link = split(' » ',$title);
						for($i = 0; $i < count($link); $i++) {
							echo '<li>'.$link[$i].'</li>';
						}
					}
					?>
					</ul>
					<input type="text" name="query" id="query" class="textbox" value="<?php echo $this->translate->_('Search')?>" onfocus="this.value=''" onblur="this.value='<?php echo $this->translate->_('Search')?>'" />
					<p><?php echo $this->translate->_('Welcome'); ?> <?php echo user::session('username') ?> | <?php echo html::anchor('default/logout', html::specialchars($this->translate->_('Log out'))) ?></p>
				</div>
			</form>
		</div>

		<div id="quickbar">
			<div id="quicklinks">
				<!--<ul>
					<li>There seems to be something wrong with your license. Click here to read more.</li>
				</ul>-->
			</div>
			<div id="icons">
				<ul>
					<li onclick="settings()" id="settings_icon"<?php if ((isset($disable_refresh) && $disable_refresh !== false) && !isset($settings_widgets)) { ?> style="display:none"<?php } ?>><?php echo html::image('application/views/themes/default/icons/16x16/settings.gif',array('alt' => $this->translate->_('Settings'), 'title' => $this->translate->_('Settings'))) ?></li>
					<li onclick="window.location.reload()"><?php echo $this->translate->_('Updated') ?>: <?php echo date('d F Y H:i:s'); ?></li>
				</ul>
			</div>
		</div>
		<div style="position: fixed">
		<div id="menu-slider"></div>
		<div id="menu-scroll">
		<div id="menu">
			<div id="close-menu" title="<?php echo $this->translate->_('Mimimize menu') ?>" onclick="collapse_menu('hide')"></div>
			<div id="show-menu" title="<?php echo $this->translate->_('Expand menu') ?>" onclick="collapse_menu('show')"></div>
			<ul>
			<?php
				foreach ($links as $header => $link):
						echo '<li class="header" onclick="collapse_section(\''.html::specialchars($header).'\')">
							<cite>'.html::specialchars($header).'</cite>
							<em>'.substr(html::specialchars($header),0,1).'</em>
						</li>'."\n";
						foreach ($link as $title => $url):
							if($url[0] == str_replace('/ninja/index.php/','',$_SERVER['PHP_SELF']))
								echo '<li class="'.html::specialchars($header).'">'.html::anchor($url[0], html::image('application/views/themes/default/icons/menu-dark/'.$url[1].'.png',array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.html::anchor($url[0], html::specialchars($title),array('style' => 'font-weight: bold', 'class' => 'ninja_menu_links')).'</li>'."\n";
							elseif($url[0] == '')
								echo '<li class="hr '.html::specialchars($header).'">&nbsp;</li>'."\n";
							else
								echo '<li class="'.html::specialchars($header).'">'.html::anchor($url[0], html::image('application/views/themes/default/icons/menu/'.$url[1].'.png',array('title' => html::specialchars($title), 'alt' => html::specialchars($title)))).' '.html::anchor($url[0], html::specialchars($title), array('class' => 'ninja_menu_links')).'</li>'."\n";
						endforeach;
					endforeach;
				?>
			</ul>
		</div>
		</div>
		</div>
		<div id="page_settings">
			<ul>
				<li class="header"><?php echo $this->translate->_('Global Settings') ?></li>
				<li id="noheader_ctrl" style="display:none">
					<input type="checkbox" id="noheader_chbx" value="1" /><label id="noheader_label" for="noheader_chbx"> <?php echo $this->translate->_('Hide page header')?></label>
				</li>
			<?php	if (!isset($disable_refresh) || $disable_refresh === false) { ?>
				<li id="ninja_page_refresh">
					<input type="checkbox" id="ninja_refresh_control" />
					<label id="ninja_refresh_lable" for="ninja_refresh_control"> <?php echo $this->translate->_('Pause refresh') ?></label>
				</li>
				<li id="ninja_refresh_edit">
					<?php echo $this->translate->_('Edit global refresh rate') ?><br />
					<div id="ninja_page_refresh_slider" style="width:200px; margin-top: 8px;">
						<input type="text" maxlength="3" size="2" id="ninja_page_refresh_value" name="ninja_page_refresh_value" style="position: absolute; font-size: 11px; margin-left: 170px; padding: 1px; margin-top:-25px;z-index: 500" />
					</div>
				</li>
				<?php
					} # end if disable_refresh

					$settings_widgets = (isset($settings_widgets)) ? $settings_widgets : '';
					if (is_array($settings_widgets)) {
						echo '<li class="header">'.$this->translate->_('Availiable Widgets').'</li>'."\n";
						foreach($settings_widgets as $id => $widget) {
							if (isset($user_widgets) && is_array($user_widgets)) {
								$class_name = array_key_exists($id, $user_widgets) ? 'selected' : 'unselected';
							} else {
								$class_name = 'selected';
							}
							echo '<li id="li_'.$id.'" class="'.$class_name.'" onclick="control_widgets(\''.$id.'\',this)">'.$widget.'</li>'."\n";
						}
						echo '<li onclick="restore_widgets();">'.$this->translate->_('Restore to factory settings').'</li>'."\n";
						echo '<li onclick="widget_page_refresh();">'.$this->translate->_('Set widget refresh rate').'</li>'."\n";
						echo '</ul>'."\n";
					}
				?>
			</ul>
		</div>

		<div id="content">
			<?php if (isset($content)) { echo $content; } else { url::redirect('tac'); } ?>
			<!--<p>Rendered in {execution_time} seconds, using {memory_usage} of memory</p> -->
		</div>
	</body>
</html>
<?php //if (extension_loaded('zlib')) { ob_end_flush(); } ?>