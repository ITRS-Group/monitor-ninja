#!/usr/bin/php
<?php
	$options = getopt("u:d:c:n:p:");
	$dbh = new PDO('mysql:host=localhost;dbname=merlin', 'root', NULL, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	

	function parse_order_setting($settings) {
		$result = array();
		$place_holders = explode('|', $settings);
		foreach($place_holders as $holder_txt) {
			list($place_holder, $order_text) = explode('=', $holder_txt);
			if(strlen($order_text)) {
				$result[$place_holder] = explode(',', $order_text);
			} else {
				$result[$place_holder] = array();
			}
			
		}
		return $result;
	}

	function encode_order_setting($settings) {
		$result = '';
		foreach($settings as $place_holder => $widgets) {
			$result .= "$place_holder=" . implode(',', $widgets) . "|";
		}
		return rtrim($result, '|');
	}

	function is_widget_isset_in_order($order, $widget) {
		foreach($order as $widgets) {
			if(in_array("widget-$widget", $widgets)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	function is_widget_isset(PDO $dbh, $name) {
		$stmt = $dbh->query("SELECT COUNT(`id`) as isset FROM `ninja_widgets` WHERE `name` = :name;");
		$stmt->execute(array("name" => $name));
		return (int)$stmt->fetchColumn() > 0;
	}

	function delete_widget(PDO $dbh, $name) {
		return $dbh->exec("DELETE FROM `ninja_widgets` WHERE `name` = {$dbh->quote($name)}");
	}

	function add_widget(PDO $dbh, $name, $friendly_name, $page) {
		return $dbh->exec("INSERT INTO `ninja_widgets` (`page`, `name`, `friendly_name`) VALUES ({$dbh->quote($page)}, {$dbh->quote($name)}, {$dbh->quote($friendly_name)})");
	}

	function update_widget(PDO $dbh, $old_name, $name, $friendly_name, $page) {
		return $dbh->prepare("UPDATE `ninja_widgets` SET `name` = :name, `friendly_name` = :friendly_name, `page` = :page WHERE `name` = :old_name;")->execute(
			array(
				'old_name' => $old_name,
				'name' => $name,
				'friendly_name' => $friendly_name,
				'page' => $page
			));	
	}

	function get_order_settings(PDO $dbh) {
		$stmt = $dbh->query("SELECT `id`, `setting` FROM `ninja_settings` WHERE `type` = 'widget_order'");
		$data = $stmt->fetchAll(PDO::FETCH_NUM);
		$result = array();
		foreach($data as $val) {
			$result[$val[0]] = $val[1];
		}
		return $result;
	}

	function set_order_setting(PDO $dbh, $id, $setting) {
		return $dbh->prepare("UPDATE `ninja_settings` SET `setting` = :setting WHERE `id` = :id;")->execute(
			array(
				'id' => $id,
				'setting' => $setting
			));
	}

	function add_widget_to_order(& $order, $name) {
		array_unshift($order['widget-placeholder'], "widget-$name");
	}

	function delete_widget_from_order(& $order, $name) {
		foreach($order as $place_holder => $widgets) {
			foreach($widgets as $wid => $wname) {
				if($wname === "widget-$name") {
					unset($order[$place_holder][$wid]);
				}
			}
		}
	}

	function replace_widgets_in_order(& $order, $from, $to) {
		foreach($order as $place_holder => $widgets) {
			foreach($widgets as $wid => $wname) {
				if($wname === "widget-$from") {
					$order[$place_holder][$wid] = "widget-$to";
				}
			}
		}	
	}

	function add_widget_to_settings($dbh, $name) {
		$settings = get_order_settings($dbh);
		foreach($settings as $id => $setting) {
			$setting = parse_order_setting($setting);
			if(!is_widget_isset_in_order($setting, $name)) {
				add_widget_to_order($setting, $name);
				if(!set_order_setting($dbh, $id, encode_order_setting($setting))) {
					die("Can`t add widget to order!\n");
				}
			}
		}
	}

	function del_widget_from_settings($dbh, $name) {
		$settings = get_order_settings($dbh);
		foreach($settings as $id => $setting) {
			$setting = parse_order_setting($setting);
			if(is_widget_isset_in_order($setting, $name)) {
				delete_widget_from_order($setting, $name);
				if(!set_order_setting($dbh, $id, encode_order_setting($setting))) {
					die("Can`t add widget to order!\n");
				}
			}
		}	
	}

	function update_widget_settings($dbh, $oldname, $name) {
		$settings = get_order_settings($dbh);
		foreach($settings as $id => $setting) {
			$setting = parse_order_setting($setting);
			if(is_widget_isset_in_order($setting, $oldname)) {
				replace_widgets_in_order($setting, $oldname, $name);
				if(!set_order_setting($dbh, $id, encode_order_setting($setting))) {
					die("Can`t add widget to order!\n");
				}
			}
		}	
	}

	array_shift($argv);
	foreach($argv as $key => $val) {
		if(strpos($val, '-') !== FALSE) {
			unset($argv[$key]);
		}
	}

	if(!sizeof($options)) {
		die("No actions.\n");
	}

	foreach(array_keys($options) as $option_key) {
		$val = $options[$option_key];
		switch($option_key) {
			case 'u':
				if(!sizeof($argv)) {
					die("You should select new widget name.\n");
				}
				$new_val = current($argv);
				if(!is_widget_isset($dbh, $val)) {
					break;
				}
				if(!isset($options['n']))  {
					die("You must set friendly name \"-n\"\n");
				}
				$page = isset($options['p']) ? $options['p'] : 'tac/index';
				if(!update_widget($dbh, $val, $new_val, $options['n'], $page)) {
					die(sprintf("Can`t update %s widget\n", $val));
				}
				update_widget_settings($dbh, $val, $new_val);
				add_widget_to_settings($dbh, $new_val);
				break;
			case 'd':
				if(is_widget_isset($dbh, $val)) {
					if(!delete_widget($dbh, $val)) {
						die(sprintf("Can`t delete %s widget\n", $val));	
					}
				}
				del_widget_from_settings($dbh, $val);
				break;
			case 'c':
				if(!is_widget_isset($dbh, $val)) {			
					if(!isset($options['n']))  {
						die("You must set friendly name \"-n\"\n");
					}
					$page = isset($options['p']) ? $options['p'] : 'tac/index';
					if(!add_widget($dbh, $val, $options['n'], $page)) {
						die(sprintf("Can`t add %s widget\n", $val));
					}
				}
				add_widget_to_settings($dbh, $val);
				break;
		}
	}
?>