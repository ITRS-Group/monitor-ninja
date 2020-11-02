<?php defined('SYSPATH') OR die('No direct access allowed.');

// View to be used for plain text file download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $content;
