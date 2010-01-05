<?php
/*

FIXME: for some reason the baseurl is changed from reports/gui/ to just reports/.
       This causes problems that are currently alleviated by quickfixes.
*/

require_once dirname(__FILE__).'/config.html2ps.inc.php';
global $HTML2PS_CACHE_DIR, $HTML2PS_WRITER_TEMPDIR, $HTML2PS_OUTPUT_FILE_DIRECTORY;
$HTML2PS_CACHE_DIR 				= HTML2PS_DIR.'/cache/';
$HTML2PS_OUTPUT_FILE_DIRECTORY 	= HTML2PS_DIR.'/out/';
$HTML2PS_WRITER_TEMPDIR 		= HTML2PS_DIR.'/temp';

require_once HTML2PS_DIR.'pipeline.factory.class.php';

# needs to be included here, else globals will not be set

#require_once '/opt/monitor/op5/gui/config.inc.php';
#require_once '/opt/monitor/op5/gui/db_config.inc.php';
require_once '/opt/monitor/op5/gui/object_relations.inc.php';

$options = array(
	'rpttimeperiod' => 'report_timeperiod',
	'scheduleddowntimeasuptime' => 'scheduled_downtime_as_uptime',
	'assumestatesduringnotrunning' => 'assume_states_during_not_running',
	'includesoftstates' => 'include_soft_states',
	'assumeinitialstates' => 'assume_initial_states'
);
$dep_vars = array(
	'assumeinitialstates' => array(
		'initialassumedhoststate' => 'initial_assumed_host_state',
		'initialassumedservicestate' => 'initial_assumed_service_state'
	)
);

$map_type_field = array(
	'hosts' => "host_name",
	'services' => "service_description",
	'hostgroups' => "hostgroup",
	'servicegroups' => "servicegroup"
);


class MyDestinationFile extends Destination {
	var $_dest_filename;

	function MyDestinationFile($dest_filename) {
		$this->_dest_filename = $dest_filename;
	}

	function process($tmp_filename, $content_type) {
		copy($tmp_filename, $this->_dest_filename);
	}
}


class MyDestinationDownload extends DestinationHTTP {
	function MyDestinationDownload($filename) {
		$this->DestinationHTTP($filename);
		$GLOBALS['PDFOutFileName'] = $filename;
	}

	function headers($content_type) {
		return array(
			"Content-Disposition: attachment; filename=".$GLOBALS['PDFOutFileName'].".".$content_type->default_extension,
			"Content-Transfer-Encoding: binary",
			"Cache-Control: must-revalidate, post-check=0, pre-check=0",
			"Pragma: public"
		);
	}
}


function is_valid_php_include($data_id, $valid_php_files)
{
	$q_pos = strpos($data_id, '?');

	if($q_pos !== false)
		$data_id = substr($data_id, 0, $q_pos);

	return !in_array($data_id, $valid_php_files);
}

function translate_report_path($data_id)
{
	if($data_id{0} != '/') # if data id is not a path, convert it to one
	{
		$data_id = ereg_replace('^http[s]?://localhost', '', $data_id);
	}

#	if(ereg('^/op5common', $data_id))
#		$data_id = "/var/www/html$data_id";

	# fixes for the illogical path handling of html2ps
	$data_id = str_replace('../', '', $data_id);
/*
	if(ereg('/reports/css', $data_id))
		$data_id = str_replace('/reports/css', '/reports/gui/css', $data_id);

	if(ereg('/reports/images', $data_id))
		$data_id = str_replace('/reports/images', '/reports/gui/images', $data_id);

	if(ereg('/reports/chart', $data_id))
		$data_id = str_replace('/reports/chart', '/reports/gui/chart', $data_id);
*/
	# @@@FIXME: we don't use this anymore?
	/*
	if(ereg('/reports/company_logo', $data_id))
		$data_id = str_replace('/reports', '/reports/gui', $data_id);
	*/
	return $data_id;
}

/*
* Function includes given php file and gathers content into a FetchedDataFile object
* @param $data_id string - Full path of php script to include
* @param inputs array - the request inputs to set
* @param baseurl string - the url of the request
* @returns FetchedDataFile
*
* NOTE: caller's responsibility to check if php file in data_id is safe to run
*/
function get_data_file_from_php($data_id, &$inputs, $baseurl)
{
	$old_wd = getcwd();
	$split_path = explode('/', $data_id);
	$script_file = array_pop($split_path);
	$path = implode('/',$split_path);

	if(strpos($script_file, '?') !== false) # has query string
	{
		$_REQUEST = array(); # clear $_REQUEST
		list($script_file, $args) = explode('?', $script_file);

		$arglist = explode('&', $args);

		foreach($arglist as $arg_assign)
		{
			if(strpos($arg_assign, '=') !== false)
			{
				list($arg_name, $arg_val) = explode('=', $arg_assign);
				$_REQUEST[$arg_name] = $arg_val;
			}
			else # arg name without assigned value
			{
				$_REQUEST[$arg_assign] = '';
			}
		}
	}

	foreach($inputs as $key => $value)
		$_REQUEST[$key] = $value;

	ob_start();

	chdir($path);
	include $script_file;

	$content = ob_get_clean();
	chdir($old_wd);

	return new FetchedDataFile($content, $baseurl);
}

function get_data_file($full_path)
{
	if(file_exists($full_path))
	{
		$data = file_get_contents($full_path);
		$path = dirname($full_path);
	}
	else
	{
		error_log("Tried to fetch unknown path = $full_path, returning empty");
		$data = '';
		$path = '';
	}

	return new FetchedDataFile($data, $path);
}


class fetcher_report extends Fetcher
{
	var $_baseurl;
	var $_report_inputs;
	var $_valid_includes;

	function __construct($baseurl, $report_inputs, $valid_includes)
	{
		$this->_baseurl = $baseurl;
		$this->_report_inputs = $report_inputs;
		$this->_valid_includes = $valid_includes;
	}

	function get_data($data_id)
	{
#		error_log("IN: data_id = $data_id");

		if(strpos($data_id, 'cgi-bin') !== false)
			return new FetchedDataFile("", $this->_baseurl);

		$data_id = translate_report_path($data_id);

		if(strpos($data_id, '.php') !== false)
		{
#			error_log("= ".substr($data_id, 0, 80));

			if(!is_valid_php_include($data_id, $this->_valid_includes))
				die('Invalid data_id in fetcher');

			return get_data_file_from_php($data_id, $this->_report_inputs, $this->_baseurl);
		}

#		error_log("+ ".substr($data_id, 0, 80));

		return get_data_file($data_id);
	}

	function get_base_url() {
		return $this->_baseurl;
	}
}

# FIXME: move out test whether baseurl is contained within $valid_includes
function create_pdf($baseurl, $report_inputs, $valid_includes)
{
	parse_html2ps_config_file(HTML2PS_DIR.'html2ps.config');
	# FIXME: don't use /tmp!
	$output_filename = tempnam('/tmp', 'autoreports_');
	$basepath = dirname($baseurl);

	$media = Media::predefined("A4");
	$media->set_landscape(false);
	$media->set_margins(array('left'   => 0,
				     'right'  => 0,
	                          'top'    => 0,
	                          'bottom' => 0));
	$media->set_pixels(1024);

	# CONFIGURATION SET FOR HTML2PS RUN
	$GLOBALS['g_config'] = array
	(
		'cssmedia'     => 'projection',
		'compress'     => true,
		'renderimages' => true,
		'renderforms'  => false,
		'renderlinks'  => false,
		'renderfields'  => false,
		'mode'         => 'html',
		'debugbox'     => false,
		'draw_page_border' => false,
		'smartpagebreak' => true,
	);

	$pipeline = new Pipeline;
	$pipeline->configure($GLOBALS['g_config']);

	$pipeline->fetchers[] = new fetcher_report($basepath, $report_inputs, $valid_includes);
	$pipeline->destination = new MyDestinationFile($output_filename);
	$pipeline->data_filters[] = new DataFilterHTML2XHTML;
	$pipeline->pre_tree_filters = array();

	/*
	$header_html    = "";
	$footer_html    = "";
	$filter = new PreTreeFilterHeaderFooter($header_html, $footer_html);
	$pipeline->pre_tree_filters[] = $filter;
	*/

	$pipeline->pre_tree_filters[] = new PreTreeFilterHTML2PSFields();
	$pipeline->parser = new ParserXHTML();
	$pipeline->layout_engine = new LayoutEngineDefault;
	$pipeline->output_driver = new OutputDriverFPDF($media);

	$pipeline->process($baseurl, $media);

	return $output_filename;
}

?>
