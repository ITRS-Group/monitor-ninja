<?php

class TestSocketMock extends PHPUnit_Framework_TestCase {

	private $pid;
	private $socketPath;

	/**
	 * Start the daemon. Couldn't be included in a setUp method due to the daemon requiring different parameters
	 * for different tests
	 *
	 * @param string $options String Option parameters for the daemon, determines what the daemon will emulate
	 */
	private function startUp($options) {
		$socketPath = tempnam(__DIR__, "mock_socket_");
		if(file_exists($socketPath)) {
			//Ugly, but will otherwise not work due to socket already taken
			unlink($socketPath);
		}

		$command = sprintf("/usr/bin/python " . __DIR__ . "/socket_mock.py %s %s", $options, $socketPath);
		$outputfile = __DIR__ . "/socket_mock.log";

		// & before ech means that the job is backgrounded
		// $! in bash means "the pid of the last backgrounded job"
		exec(sprintf("%s > %s 2>&1 & echo $!", $command, $outputfile), $output, $exitCode);
		$this->assertSame(0, $exitCode, "Could not start the daemon at $socketPath");
		$this->pid = $output[0];

		//Wait until the daemon process has started; max waiting time = 5 seconds
		$daemonStart = time();
		while (!file_exists($socketPath) && ($daemonStart - time()) < 5)
			continue;
		$this->assertFileExists($socketPath, "Could not create socket at $socketPath, after trying multiple times");
		$this->socketPath = $socketPath;
	}

	public function test_daemon_can_emulate_timeout_after_getting_written_to() {
		$this->startUp("--no-answer");

		$handle = stream_socket_client("unix://" . $this->socketPath);
		$this->assertNotSame(false, $handle, "Could not create a socket at $this->socketPath");

		$timeout = stream_set_timeout($handle, 1);
		$this->assertTrue($timeout, "Could not set timeout for socket at $this->socketPath");

		$metaData = stream_get_meta_data($handle);
		$this->assertSame(false, $metaData['timed_out']);

		$written = fwrite($handle, "GET 123");
		$this->assertNotSame(false, $written, "Could not write to socket at $this->socketPath");

		fread($handle, 512);
		$metaData = stream_get_meta_data($handle);
		$this->assertSame(true, $metaData['timed_out']);
	}

	public function test_daemon_can_return_custom_response() {
		$this->startUp("--custom-answer=Banana");
		$handle = stream_socket_client("unix://" . $this->socketPath);
		$this->assertNotSame(false, $handle, "Could not create a socket at $this->socketPath");

		$timeout = stream_set_timeout($handle, 1);
		$this->assertTrue($timeout, "Could not set timeout for socket at $this->socketPath");

		$written = fwrite($handle, "GET 123");
		$this->assertNotSame(false, $written, "Could not write to socket at $this->socketPath");

		$response = fread($handle, 512);

		$this->assertSame("Banana", $response);
	}

	public function tearDown() {
		if ($this->socketPath) {
			exec(sprintf("kill -9 %d", $this->pid));
			/* If the log file was created, remove it; could
				* possibly use this as a hook for submitting
				* these logs to the build system, if needed */
			echo file_get_contents(__DIR__ . "/socket_mock.log");
			unlink($this->socketPath);
		}
	}

}
