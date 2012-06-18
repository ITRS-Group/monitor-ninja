<?php

/**
 * Set $config['log_benchmark_to_file'] = '/tmp/ninja_benchmark.log' in
 * ninja/application/config/custom/config.php
 * to log benchmarking data.
 */
class benchmark_log {
	private $filename;

	/**
	 * @param $filename string
	 */
	function __construct($filename) {
		$this->filename = $filename;
		Event::add('system.display', array($this, 'log_to_file'));
	}

	/**
	 * Collects benchmarking information and prints to file
	 */
	function log_to_file() {
		$benchmark = Benchmark::get(SYSTEM_BENCHMARK.'_total_execution');
		$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

		$output = array(
			'timestamp' => time(),
			'user' => Auth::instance()->get_user()->username,
			'url' => url::current(true),
			'execution_time' => $benchmark['time'].'s',
			'memory_usage' => number_format($memory, 2).'MB'
		);

		file_put_contents($this->filename, implode(' ', $output)."\n", FILE_APPEND);
	}
}

if($log_benchmark_to_file = Kohana::config('config.log_benchmark_to_file')) {
	new benchmark_log($log_benchmark_to_file);
}
