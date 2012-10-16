<?php defined('SYSPATH') OR die('No direct access allowed.');

	$authorized = false;
	if (Auth::instance()->logged_in()) {
		$ninja_menu_setting = Ninja_setting_Model::fetch_page_setting('ninja_menu_state', '/');

		$auth = Nagios_auth_Model::instance();
		if ($auth->view_hosts_root) {
			$authorized = true;
		}
	}

	if (isset($this) && isset($this->template->js_header))
		$this->template->js_header->js = $this->xtra_js;

?>
<!DOCTYPE html>
<html>
	
	<?php
		include_once(__DIR__.'/template_head.php');
	?>

	<body>

		<div class="container">
			<div class="logo">
				<?php echo html::image('application/views/themes/default/icons/op5.gif', array('style' => 'float: left; margin-left: 15px;')); ?>
			</div>

			<?php
				include_once(__DIR__.'/template_header.php');
			?>

			<div class="navigation" id="navigation">
				
				<form action="<?php echo Kohana::config('config.site_domain') ?><?php echo Kohana::config('config.index_page') ?>/search/lookup" id="global_search" method="get">
					<?php
					$query = arr::search($_REQUEST, 'query');
					if ($query !== false && Router::$controller == 'search' && Router::$method == 'lookup') { ?>
						<input type="text" name="query" id="query" class="textbox" value="<?php echo $query ?>" />
					<?php } else { ?>
						<input type="text" name="query" id="query" class="textbox" value="<?php echo _('Search')?>" onfocus="this.value=''" onblur="this.value='<?php echo _('Search')?>'" />
					<?php	} ?>
					<?php try { echo help::render('search_help', 'search'); } catch (Zend_Exception $ex) {} ?>
				</form>
				<div class="menu" id="main-menu">

				<?php
					include_once(__DIR__.'/template_menu.php');
				?>

				</div>
				<div class="slider" id="slider" title="Collapse Navigation">
					<div class="slide-button">
						::
					</div>
				</div>

			</div>

			<div class="content" id="content">
				
				
					<?php if (isset($content)) { echo $content; } else { url::redirect(Kohana::config('routes.logged_in_default')); }?>
				
			</div>
			
			<?php 

				if (isset($saved_searches) && !empty($saved_searches)) {
					echo $saved_searches;
				}
				
			?>

			<div id="save-search-form" title="<?php echo _('Save search') ?>" style="display:none">
				<form>
				<p class="validateTips"></p>
				<fieldset>
					<label for="search_query"><?php echo _('Search string') ?></label>
					<input type="text" name="search_query" id="search_query" value="<?php echo isset($query_str) ? $query_str : '' ?>" class="texts search_query ui-widget-content ui-corner-all" />
					<label for="search_name"><?php echo _('Name') ?></label>
					<input type="text" name="search_name" id="search_name" class="texts ui-widget-content ui-corner-all" />
					<label for="search_description"><?php echo _('Description') ?></label>
					<textarea cols="30" rows="3" name="search_description" id="search_description" class="texts ui-widget-content ui-corner-all"></textarea>
					<input type="hidden" name="search_id" id="search_id" value="0">
				</fieldset>
				</form>
			</div>

		</div>
		<?php
			echo html::script('application/media/js/dojo.js');
		?>

	</body>
</html>
