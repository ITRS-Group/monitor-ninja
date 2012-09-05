<?php defined('SYSPATH') OR die('No direct access allowed.');

class Pdf_Controller extends Authenticated_Controller
{
	public function generate($type, $id)
	{
		exec("/usr/bin/php /root/Source/monitor/ninja/index.php $type/generate?report_id=$id monitor | /root/Source/monitor/wkhtmltopdf/static-build/linux-local/wkhtmltopdf/bin/wkhtmltopdf --print-media-type - -", $output, $code);
		header('Content-Type: application/pdf');
		print implode("\n", $output);
	}
}
