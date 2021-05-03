<?php

class TestSocketMock extends \PHPUnit\Framework\TestCase {

	private $procH;
	private $pipes;
	private $socketPath;

	/**
	 * Start the daemon. Couldn't be included in a setUp method due to the daemon requiring different parameters
	 * for different tests
	 *
	 * @param string $options String Option parameters for the daemon, determines what the daemon will emulate
	 */
	private function startUp($options) {
		$socketPath = tempnam(__DIR__, "mock_socket_");
		$command = sprintf(__DIR__ . "/socket_mock.py %s %s", $options, $socketPath);

		$descriptorSpec = array(
			array('pipe', 'r'),
			array('pipe', 'w'),
			array('file', '/dev/null', 'a')
		);
		$this->procH = proc_open($command, $descriptorSpec, $pipes);
		$this->pipes = $pipes;

		//Wait until the daemon process has started; max waiting time = 5 seconds
		$daemonStart = time();
		while (!file_exists($socketPath) && ($daemonStart - time()) < 5) {
			usleep(50);
			continue;
		}
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
		$this->assertFalse($metaData['timed_out']);

		$written = fwrite($handle, "GET 123");
		$this->assertNotSame(false, $written, "Could not write to socket at $this->socketPath");

		$this->assertSame('', fread($handle, 512));

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

	public function tearDown() : void {
		foreach($this->pipes as $pipe) {
			fclose($pipe);
		}
		$details = proc_get_status($this->procH);
		posix_kill($details['pid'], "9");
		proc_close($this->procH);
		unlink($this->socketPath);
	}

}
