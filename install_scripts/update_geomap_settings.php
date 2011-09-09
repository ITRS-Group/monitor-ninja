<?php

/**
 *	Post-install script for geomap 2.0
 *	Takes old settings and merge it to new settings files
 */

define("FILE_LINKS", 'links.xml');
define("FILE_LOCATIONS", 'locations.xml');
define("FILE_VIEWPOINTS", 'viewpoints.xml');

//------------------------------------------------------------------------------

echo "Merge settings script:\n";

if (!isset($argv[1])) {
	define("DIR_OLD_SETTINGS", '/opt/monitor/op5/nagvis/etc/geomap/');
	define("DIR_SETTINGS", '/opt/monitor/op5/ninja/application/config/geomap/');
	echo "use default destination to settings files:\n" . DIR_OLD_SETTINGS . " and " . DIR_SETTINGS . "\n";
}
else
{
	if (!isset($argv[2])) {
		exit("Error: No new(second) destination. Exit.");
	}
	if (check($argv[1]) or check($argv[2])) {
		exit("Error: Wrong destination address (forgot '/')");
	}
	define("DIR_OLD_SETTINGS", $argv[1]);
	define("DIR_SETTINGS", $argv[2]);
	echo "dirs:" . DIR_OLD_SETTINGS . " and ".DIR_SETTINGS . "\n";
}

if (merge_files(FILE_LINKS) && merge_files(FILE_LOCATIONS) && merge_files(FILE_VIEWPOINTS))
	echo "Complete success!\n";

//------------------------------------------------------------------------------

function merge_files( $file )
{
	if (file_exisits(DIR_OLD_SETTINGS . $file) === FALSE) {
		print "Warring: Failed to open file:" . DIR_OLD_SETTINGS.$file . "\n";
		return FALSE;
	}

	if (($old = simplexml_load_file(DIR_OLD_SETTINGS . $file)) === FALSE) {
		print "Warring: Failed to parse xml file:" . DIR_OLD_SETTINGS.$file . "\n";
		return FALSE;
	}

	if (file_exists(DIR_SETTINGS . $file) === FALSE) {
		print "Warring: Failed to open file:" . DIR_SETTINGS.$file . "\n";
		return FALSE;
	}
	if ( !is_writable(DIR_SETTINGS . $file)) {
		print "Warring: File:" . DIR_SETTINGS.$file . " is not writable\n";
		return FALSE;
	}

	if (($xml = simplexml_load_file(DIR_SETTINGS . $file)) === FALSE) {
		print "Warring: Failed to parse xml file:" . DIR_SETTINGS.$file . "\n";
		return FALSE;
	}
	
	append_to_xml($xml, $old);
	if (file_put_contents(DIR_SETTINGS . $file, $xml->asXml()) === FALSE) {
		print "Error: Fail to write file:" . DIR_SETTINGS.$file . "\n";
		return FALSE;
	}
	return TRUE;
}


function check($str)
{
	return $str[strlen($str)-1]=='/'?0:1;
}

function append_to_xml(&$xml_to, &$xml_from)
{
	foreach ($xml_from->children() as $xml_child) {
		$xml_temp = $xml_to->addChild($xml_child->getName(), (string) $xml_child);
		
		foreach ($xml_child->attributes() as $key => $value) {
			$xml_temp->addAttribute($key, $value);
		}
	append_to_xml($xml_temp, $xml_child);
	}
}

?>
