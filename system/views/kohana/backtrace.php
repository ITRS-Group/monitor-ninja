<?php defined('SYSPATH') OR die('No direct access allowed.');

$arg_badword = array(
	'passw', /* password and passwd */
	'pwd',
	'secret'
);


if ( ! is_array($trace))
	return;

echo '<ul class="backtrace">';

foreach ($trace as $entry)
{
	echo '<li>';

	if (isset($entry['file']))
	{
		echo Kohana::lang('core.error_file_line', preg_replace('!^'.preg_quote(DOCROOT).'!', '', $entry['file']), $entry['line']);
	}

	echo '<pre>';

	$reflclass = false;
	if (isset($entry['class']))
	{
		// Add class and call type
		echo $entry['class'].$entry['type'];
		try {
			$reflclass = new ReflectionClass($entry['class']);
		} catch( Exception $e ) {
			// Don't care about the problem... just don't expand variable names in that case
			$reflclass = false;
		}
	}

	// Add function
	echo $entry['function'].'( ';

	$reflmethod = false;
	try {
		if( $reflclass )
			$reflmethod = $reflclass->getMethod($entry['function']);
		else
			$reflmethod = new ReflectionFunction($entry['function']);
	} catch( Exception $e ) {
		// Don't care about the problem... just don't expand variable names in that case
		$reflmethod = false;
	}
	// Add function args
	if (isset($entry['args']) AND is_array($entry['args']))
	{
		// Separator starts as nothing
		$sep = '';

		$reflargs = false;
		try {
			if( $reflmethod )
				$reflargs = $reflmethod->getParameters();
		} catch( Exception $e ) {
			// Don't care about the problem... just don't expand variable names in that case
			$reflargs = false;
		}

		while ($arg = array_shift($entry['args']))
		{
			$argname = "...";
			try {
				if( !empty($reflargs) )
					$argname = array_shift( $reflargs )->getName();
			} catch( Exception $e ) {
				// Don't care about the problem... just don't expand variable names in that case
				$argname = "...";
			}

			if (is_string($arg) AND substr($arg, 0, 4) !== "unix" AND is_file($arg))
			{
				// Remove docroot from filename
				$arg = preg_replace('!^'.preg_quote(DOCROOT).'!', '', $arg);
			}

			foreach($arg_badword as $badword) {
				if( stripos($argname, $badword) !== false )
					$arg = "*****";
			}

			echo $sep.$argname.' = '.html::specialchars(print_r($arg, TRUE));

			// Change separator to a comma
			$sep = ', ';
		}
	}

	echo " )</pre></li>\n";
}

echo '</ul>';