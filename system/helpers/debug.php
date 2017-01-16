<?php
/**
 * Contains helper functions for getting debug info.
 */
class debug {

	/**
	 * Get the parameter names of the given function.
	 *
	 * @param $function string      The name of the function.
	 * @param $class    string|null The name of the class containing $function,
	 *                              or null (optional).
	 * @returns         array       An array of parameter names, or an empty
	 *                              array if no parameter names are found or
	 *                              class/function does not exist.
	 */
	public static function get_func_param_names($function, $class = null) {
		if ($class) {
			try {
				$reflclass = new ReflectionClass($class);
				$reflmethod = $reflclass->getMethod($function);
			} catch (ReflectionException $e) {
				// Don't care about the problem.
			}
		}

		try {
			if (!isset($reflmethod)) {
				$reflmethod = new ReflectionFunction($function);
			}
			$params = $reflmethod->getParameters();
		} catch (ReflectionException $e) {
			// Don't care about the problem.
			// We cannot get any params info. Return empty list.
			return array();
		}

		// Get the name for each parameter.
		return array_map(function($v) { return $v->name; }, $params);
	}

	/**
	 * This function emulates print_r($data, true) but is "safe" in the way
	 * that it does not expand varible depth into infinity.
	 *
	 * @param $data    mixed  The data that should outputted as a string.
	 * @param $nesting int    Maximum nesting level.
	 * @param $intent  string Only used internally.
	 *
	 * @returns        string $data formatted as a string.
	 */
	public static function safe_print_r($data, $nesting = 5, $indent = '') {
		ob_start();
		if ($nesting < 0) {
	        echo "** MORE **";
		} else if (is_object($data) || is_array($data)) {
			$type = gettype($data);
	        echo $type === 'object' ? get_class($data) : ucfirst($type);
	        echo '(';
	        $data = (array) $data;
	        end($data);
	        $last = key($data);
	        foreach ($data as $k => $v) {
	            echo $indent . "[$k] => ";
	            echo self::safe_print_r($v, $nesting - 1, "$indent ");
	            if ($k !== $last)
	            	echo ', ';
	        }
	        echo "$indent)";
	    } else {
	        print_r($data);
	    }
	    return ob_get_clean();
	}

	/**
	 * Formats the given backtrace.
	 * - "DOCROOT" is removed from file paths
	 * - Function arguments are expanded with argument names (not only values)
	 *   and arguments that are likely to be a passwords are masked.
	 *
	 * @param $backtrace array  Backtrace as return by debug_backtrace() or
	 *                          Exception::getTrace().
	 * @param $docroot   string Path to document root (optional).
	 *
	 * @returns          array An array with semi-formated backtrace info.
	 */
	public static function format_backtrace(array $backtrace, $docroot = DOCROOT) {

		$args_to_mask = array(
			'passw', // password and passwd
			'pwd',
			'secret'
		);

		$output = array();
		foreach ($backtrace as $entry) {
			$output_line = array();
			if (isset($entry['file'])) {
				$output_line['file'] =
					preg_replace('!^' . preg_quote($docroot) . '!', '', $entry['file']);
				$output_line['line'] = $entry['line'];
			}

			$class = isset($entry['class']) ? $entry['class'] : null;
			$output_line['class'] = $class;
			$output_line['type'] = isset($entry['type']) ? $entry['type'] : null;
			$output_line['function'] = $entry['function'];
			$output_line['args'] = array();

			if (!isset($entry['args'])) {
				$output[] = $output_line;
				continue;
			}

			// Get parameter names from the function in the stack trace entry.
			$arg_names = debug::get_func_param_names($entry['function'], $class);

			// Format the data from each argument.
			$num_args = count($entry['args']);
			for ($i = 0; $i < $num_args; $i++) {
				$arg = $entry['args'][$i];

				if (is_string($arg) && substr($arg, 0, 4) !== "unix" && @is_file($arg)) {
					// Remove docroot from filename.
					$arg = preg_replace('!^' . preg_quote($docroot) . '!', '', $arg);
				}

				$arg_name = isset($arg_names[$i]) ? $arg_names[$i] : '...';

				foreach ($args_to_mask as $bad_word) {
					if (stripos($arg_name, $bad_word) !== false)
					$arg = '*****';
				}

				$output_line['args'][] = $arg_name . ' = ' . debug::safe_print_r($arg);
			}
			$output[] = $output_line;
		}
		return $output;
	}

	/**
	 * Prints the info in $backtrace as html. Function arguments within the
	 * backtrace with names that are likely to be a password are masked.
	 * Note that this function has a depencency upon Kohana::lang().
	 *
	 * @param $backtrace array  Backtrace as return by debug_backtrace() or
	 *                          Exception::getTrace().
	 * @param $docroot   string Path to document root (optional).
	 */
	public static function print_backtrace_as_html(array $backtrace, $docroot = DOCROOT) {
		$formatted_bt = debug::format_backtrace($backtrace, $docroot);

		echo '<ul class="backtrace">';
		foreach ($formatted_bt as $line) {
			?>
			<li>
				<?php
				if (isset($line['file'])) {
					echo Kohana::lang(
						'core.error_file_line', $line['file'], $line['line']
					);
				}
				?>
				<pre>
					<?php
					if (isset($line['class'])) {
						echo $line['class'] . $line['type'];
					}
					echo $line['function'] . '( ',
						html::specialchars(implode(', ', $line['args'])),
						' )';
					?>
				</pre>
			</li>
			<?php
		}
		echo '</ul>';
	}

	/**
	 * Formats a backtrace as a string. Function arguments within the backtrace
	 * with names that are likely to be a password are masked.
	 * @param $backtrace array  Backtrace as return by debug_backtrace() or
	 *                          Exception::getTrace().
	 * @param $docroot   string Path to document root (optional).
	 *
	 * @returns          string A string as human readable text.
	 */
	public static function get_backtrace_as_string(array $backtrace, $docroot = DOCROOT) {
		$formatted_bt = debug::format_backtrace($backtrace, $docroot);

		$output = '';
		foreach ($formatted_bt as $line) {
			if (isset($line['file'])) {
				$output .= $line['file'] . " [{$line['line']}]: ";
			}

			if (isset($line['class'])) {
				$output .= $line['class'] . $line['type'];
			}

			$output .= $line['function'] . '( ' .
				implode(', ', $line['args']) .
				" )\n";
		}
		// Remove last newline.
		return substr($output, 0, -1);
	}
}