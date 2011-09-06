<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *	Specify the path to the hyperapplet jar file
 * 	Download from http://hypergraph.sourceforge.net/download.html
 */

$config['hypergraph_dir'] = 'vendor/hypergraph';


$config['hyperapplet_path'] = $config['hypergraph_dir'] . '/hyperapplet.jar';
$config['nagios_props'] = $config['hypergraph_dir'] . '/nagios.prop';
$config['hyper_dtd'] = $config['hypergraph_dir'] . '/GraphXML.dtd';

if (!is_file(APPPATH.$config['hyperapplet_path']))
	$config['hyperapplet_path'] = false;
if (!is_file(APPPATH.$config['nagios_props']))
	$config['nagios_props'] = false;
if (!is_file(APPPATH.$config['hyper_dtd']))
	$config['hyper_dtd'] = false;

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
