<?php

class TestSocketMock extends PHPUnit_Framework_TestCase {

    private $pid;
    private $socketPath;

    /**
     * Start the daemon. Couldn't be included in a setUp method due to the daemon requiring different parameters
     * for different tests
     * @param string $options String Option parameters for the daemon, determines what the daemon will emulate
     */
    private function startUp($options="") {

        $this->socketPath = tempnam(__DIR__, "mock_socket_");
        unlink($this->socketPath); //Ugly, but will otherwise not work due to socket already taken

        $command = sprintf("/usr/bin/python " . __DIR__ . "/socket_mock.py %s %s", $options, $this->socketPath);
        $outputfile = __DIR__ . "/socket_mock.log";
        exec(sprintf("%s > %s 2>&1 & echo $!", $command, $outputfile), $pidArr);
        $this->pid = $pidArr[0];

        //Wait until the daemon process has started; max waiting time = 5 seconds
        $daemonStart = time();
        while (!file_exists($this->socketPath) && ($daemonStart - time()) < 5)
            continue;
    }

    /**
     * Tests the ability of the daemon to emulate a response that times out
     */
    public function test_no_answer_after_message() {
        $this->startUp("--no-answer");
        $handle = stream_socket_client("unix://" . $this->socketPath);
        stream_set_timeout($handle, 1);
        $metaData = stream_get_meta_data($handle);
        $this->assertNotEquals(1, $metaData['timed_out']);

        fwrite($handle, "GET 123");
        fread($handle, 512);
        $metaData = stream_get_meta_data($handle);
        $this->assertEquals(1, $metaData['timed_out']);
    }

    /**
     * Make sure that the daemon returns the response supplied
     */
    public function test_custom_response() {
        $this->startUp("--custom-answer=Banana");
        $handle = stream_socket_client("unix://" . $this->socketPath);
        stream_set_timeout($handle, 1);
        fwrite($handle, "GET 123");
        $response = fread($handle, 512);

        $this->assertEquals("Banana", $response);
    }

    public function tearDown() {
        if ($this->socketPath) {
            exec(sprintf("kill -9 %d", $this->pid));
            /* If the log file was created, remove it; could possibly use this as a hook for submitting these logs to the
            build system, if needed */
            if (file_exists(__DIR__ . "/socket_mock.log"))
                unlink(__DIR__ . "/socket_mock.log");
            unlink($this->socketPath);
        }
    }

}
