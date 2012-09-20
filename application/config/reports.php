<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package reports
 *
 * Sender of reports
 * Enter name of sender of reports here or it will be
 * something like <product_name>@<hostname>
 */
$config['from'] = false;

/**
 * email of sender
 */
$config['from_email'] = '';

/**
*	Path to showlog executable
*/
$config['showlog_path'] = '/opt/monitor/op5/merlin/showlog';

/**
 * Command for converting the HTML output from a report to a PDF
 *
 * Notes:
 *  --disable-javascript makes our js not muck up our layout, and without it,
 *   the text sometimes becomes invisible.
 *  --disable-smart-shrinking makes things float less weirdly.
 *  --disable-*-links means there won't be broken links in the pdf
 *
 *  https://code.google.com/p/wkhtmltopdf/issues/detail?id=957 is worked around
 *  with --{top,bottom}-spacing and --{header,footer}-html
 */
$config['pdf_command'] = '/usr/bin/wkhtmltopdf -q --print-media-type --disable-javascript --disable-smart-shrinking --disable-internal-links --disable-external-links --margin-top 10 --margin-bottom 10 --header-spacing 5 --footer-spacing 5 --header-html application/media/report_header.html --footer-html application/media/report_footer.html - -';
