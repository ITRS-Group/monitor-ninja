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
		$this->template->js_header->js = array_unique($this->xtra_js);

	/*if (!($p = popen("ifconfig","r"))) return false;
		$out = "";
			
	while(!feof($p))
		$out .= fread($p,1024);
			
	fclose($p);*/

	$server_ip = $_SERVER['SERVER_ADDR'];

	$match  = "/^.*".$server_ip;
	$match .= ".*Bcast:(\d{1,3}\.\d{1,3}i\.\d{1,3}\.\d{1,3}).*";
	$match .= "Mask:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/im";
	//$match .= "Mask:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/im";
	
	//var_dump($out);

	//echo "<br /><br />";

	//var_dump($match);

	/*
		string(1297) "lo0: flags=8049 mtu 16384 options=3 inet6 fe80::1%lo0 prefixlen 64 scopeid 0x1 inet 127.0.0.1 netmask 0xff000000 inet6 ::1 prefixlen 128 gif0: flags=8010 mtu 1280 stf0: flags=0<> mtu 1280 en0: flags=8963 mtu 1500 options=27 ether 34:15:9e:19:c6:ee inet6 fe80::3615:9eff:fe19:c6ee%en0 prefixlen 64 scopeid 0x4 inet 192.168.1.23 netmask 0xffffff00 broadcast 192.168.1.255 media: autoselect (1000baseT ) status: active en1: flags=8863 mtu 1500 ether f8:1e:df:e4:43:70 inet6 fe80::fa1e:dfff:fee4:4370%en1 prefixlen 64 scopeid 0x5 inet 192.168.1.165 netmask 0xffffff00 broadcast 192.168.1.255 media: autoselect status: active fw0: flags=8863 mtu 4078 lladdr 34:15:9e:ff:fe:19:c6:ee media: autoselect status: inactive p2p0: flags=8843 mtu 2304 ether 0a:1e:df:e4:43:70 media: autoselect status: inactive vboxnet0: flags=8843 mtu 1500 ether 0a:00:27:00:00:00 inet 192.168.56.1 netmask 0xffffff00 broadcast 192.168.56.255 " 
		string(99) "/^.*::1.*Bcast:(\d{1,3}\.\d{1,3}i\.\d{1,3}\.\d{1,3}).*Mask:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/im"
	*/

	//if (!preg_match($match, $out, $regs))
	//	return false;

	//var_dump('passed');

	//die;

?>
<!DOCTYPE html>
<html>

	<?php
		require __DIR__.'/template_head.php';
	?>

	<body>

		<div class="container">

			<?php
				require __DIR__.'/template_header.php';
			?>

			<div class="navigation" id="navigation">

				<div class="menu" id="main-menu">

				<?php
					require __DIR__.'/template_menu.php';
				?>

				</div>

			</div>

			<div class="content" id="content">


					<?php if (isset($content)) { echo $content; } else { return url::redirect(Kohana::config('routes.logged_in_default')); }?>

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
			if (isset($context_menu))
				echo $context_menu;
		?>

	</body>
</html>
