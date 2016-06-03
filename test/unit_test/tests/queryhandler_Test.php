<?php

class Queryhandler_Test extends PHPUnit_Framework_TestCase {
	public function test_kvvec2array_one() {
		$this->assertSame(array('foo' => 'bar'),
			op5queryhandler::kvvec2array('foo=bar;'));
	}

	public function test_kvvec2array_two() {
		$this->assertSame(array('foo' => 'bar', 'baz' => 'quz'),
			op5queryhandler::kvvec2array('foo=bar;baz=quz'));
	}

	public function test_kvvec2array_duplicate_key() {
		$this->assertSame(array('foo' => 'baz'),
			op5queryhandler::kvvec2array('foo=bar;foo=baz;'));
	}

	public function test_kvvec2array_empty_value() {
		$this->assertSame(array('foo' => ''),
			op5queryhandler::kvvec2array('foo='));
	}

	public function test_kvvec2array_empty_key() {
		$this->assertSame(array('' => 'foo'),
			op5queryhandler::kvvec2array('=foo'));
	}

	/**
	 * @expectedException op5queryhandler_Exception
	 */
	public function test_kvvec2array_key_syntax_error() {
		op5queryhandler::kvvec2array('foo;');
	}

	/**
	 * @expectedException op5queryhandler_Exception
	 */
	public function test_kvvec2array_value_syntax_error() {
		op5queryhandler::kvvec2array('foo=bar=wot');
	}

	public function test_kvvec2array_escaped() {
		$this->assertSame(array('foo;bar' => 'baz'),
			op5queryhandler::kvvec2array('foo\;bar=baz'));

		$this->assertSame(array('foo' => 'baz'),
			op5queryhandler::kvvec2array('foo=baz\\'));

		$this->assertSame(array('foo=bar' => 'baz'),
			op5queryhandler::kvvec2array('foo\\=bar=baz'));

		$this->assertSame(array('foo=bar' => 'baz', 'qux' => 'xyzzy'),
			op5queryhandler::kvvec2array('foo\\=bar=baz;qux=xyzzy;'));

		$this->assertSame(array("foo=\nbar" => "ba\rz", "qu\t\tx" => 'xyz\\zy'),
			op5queryhandler::kvvec2array('foo\\=\nbar=ba\rz;qu\t\tx=xyz\\\\zy;'));
	}

	public function test_kvvec2array_real_life_example() {
		$s = 'job_id=19718;type=0;command=/opt/plugins/suid/check_host -h;timeout=60;wait_status=768;start=1464957908.193606;stop=1464957908.194494;runtime=0.000888;exited_ok=1;ru_utime=0.000000;ru_stime=0.000000;ru_minflt=223;ru_majflt=0;ru_inblock=0;ru_oublock=0;outerr=;outstd=Usage: check_host [options] [-H] host1 host2 hostn\n\nWhere options are any combination of:\n * -H | --host      specify a target\n * -w | --warn      warning threshold (currently 2000.000ms,100%)\n * -c | --crit      critical threshold (currently 2000.000ms,100%)\n * -n | --packets   number of packets to send (currently 5, max is 64)\n * -i | --interval  packet interval (currently 200.000ms)\n * -I | --hostint   target interval (currently 0.000ms)\n * -l | --ttl       outgoing TTL (currently 64, not supported on all platforms)\n * -t | --timeout   timeout value (seconds, currently  10)\n * -b | --bytes     icmp packet size (currenly ignored)\n   -v | --verbose   verbosity++ (4 is sort of max)\n   -h | --help      this cruft\n\nThreshold format for -w and -c is 200.25,60% for 200.25 msec RTA and 60%\npacket loss.  All threshold values match inclusively.\nYou can specify different RTA factors using the standardized abbreviations\nus (microseconds), ms (milliseconds, default) or just plain s for seconds.\n\nOptions marked with * requires an argument.\n\nLong options are currently unsupported.\n\nIf this program is invoked as check_host (with a symlink for example), it will\nexit with status OK upon the first properly received ICMP_ECHOREPLY. This makes\nit ideal for hostchecks, which usually return OK and needs to run fast.\n\n';

		$expected = array(
			"job_id" => "19718",
			"type" => "0",
			"command" => "/opt/plugins/suid/check_host -h",
			"timeout" => "60",
			"wait_status" => "768",
			"start" => "1464957908.193606",
			"stop" => "1464957908.194494",
			"runtime" => "0.000888",
			"exited_ok" => "1",
			"ru_utime" => "0.000000",
			"ru_stime" => "0.000000",
			"ru_minflt" => "223",
			"ru_majflt" => "0",
			"ru_inblock" => "0",
			"ru_oublock" => "0",
			"outerr" => "",
			"outstd" => "Usage: check_host [options] [-H] host1 host2 hostn\n\nWhere options are any combination of:\n * -H | --host      specify a target\n * -w | --warn      warning threshold (currently 2000.000ms,100%)\n * -c | --crit      critical threshold (currently 2000.000ms,100%)\n * -n | --packets   number of packets to send (currently 5, max is 64)\n * -i | --interval  packet interval (currently 200.000ms)\n * -I | --hostint   target interval (currently 0.000ms)\n * -l | --ttl       outgoing TTL (currently 64, not supported on all platforms)\n * -t | --timeout   timeout value (seconds, currently  10)\n * -b | --bytes     icmp packet size (currenly ignored)\n   -v | --verbose   verbosity++ (4 is sort of max)\n   -h | --help      this cruft\n\nThreshold format for -w and -c is 200.25,60% for 200.25 msec RTA and 60%\npacket loss.  All threshold values match inclusively.\nYou can specify different RTA factors using the standardized abbreviations\nus (microseconds), ms (milliseconds, default) or just plain s for seconds.\n\nOptions marked with * requires an argument.\n\nLong options are currently unsupported.\n\nIf this program is invoked as check_host (with a symlink for example), it will\nexit with status OK upon the first properly received ICMP_ECHOREPLY. This makes\nit ideal for hostchecks, which usually return OK and needs to run fast.\n\n"
		);

		$this->assertSame($expected, op5queryhandler::kvvec2array($s));
	}

	public function test_kvvec2array_insanely_long_string() {
		$s = 'job_id=19607;type=0;command=/opt/plugins/check_wmi_plus.pl -h;timeout=60;wait_status=768;start=1464956366.065237;stop=1464956366.518738;runtime=0.453501;exited_ok=1;ru_utime=0.262960;ru_stime=0.134979;ru_minflt=4673;ru_majflt=7;ru_inblock=4656;ru_oublock=0;outerr=;outstd=' . str_repeat("x", 20000);
		$expected = array(
			"job_id" => "19607",
			"type" => "0",
			"command" => "/opt/plugins/check_wmi_plus.pl -h",
			"timeout" => "60",
			"wait_status" => "768",
			"start" => "1464956366.065237",
			"stop" => "1464956366.518738",
			"runtime" => "0.453501",
			"exited_ok" => "1",
			"ru_utime" => "0.262960",
			"ru_stime" => "0.134979",
			"ru_minflt" => "4673",
			"ru_majflt" => "7",
			"ru_inblock" => "4656",
			"ru_oublock" => "0",
			"outerr" => "",
			"outstd" => str_repeat("x", 20000)
);
		$this->assertSame($expected, op5queryhandler::kvvec2array($s));
	}
}
