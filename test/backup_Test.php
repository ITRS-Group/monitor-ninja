<?php
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Backup_Test extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->markTestSkipped(
			'I think there was something about permissions that was a problem'
		);
		Auth::instance(array('session_key' => false))->force_user(new User_AlwaysAuth_Model());
		$this->pre_backups = array();
		$this->backup_location = "/var/www/html/backup";
		if ($handle = opendir($this->backup_location)) {
			while (false !== ($entry = readdir($handle))) {
				$this->pre_backups[] = $entry;
			}
		}
	}

	public function tearDown() {
		#Remove any backups created since we started
		if ($handle = opendir($this->backup_location)) {
			while (false !== ($entry = readdir($handle))) {
				if(!in_array($entry, $this->pre_backups))
					unlink($this->backup_location.'/'.$entry);
			}
		}
	}

	public function test_backup() {
		$controller = new Backup_Controller();
		$controller->backup();
		$this->ok(isset($controller->template->status) && $controller->template->status,
			"asserting backup success: returned {$controller->template->message}\nFull output: ".$controller->debug);
		$this->ok($controller->template->file != '', "asserting backup file has been set");
	}

	public function test_backup_restore() {
		$controller = new Backup_Controller();
		$controller->backup();
		$this->ok(isset($controller->template->status) && $controller->template->status,
			"asserting backup success: returned {$controller->template->message}\nFull output: ".$controller->debug);
		$this->ok($controller->template->file != '', "asserting backup file has been set");
		$this_backup = $controller->template->file;
		$controller->restore($this_backup);
		$this->ok(isset($controller->template->status) && $controller->template->status,
			"asserting restore success: returned {$controller->template->message}\nFull output: ".$controller->debug);
		sleep(5); // *sigh* Wait for nagios to start again...
	}

	public function test_backup_delete() {
		$controller = new Backup_Controller();
		$controller->backup();
		$this_backup = $controller->template->file;

		$this->ok(file_exists($this->backup_location . '/' . $this_backup . '.tar.gz'), "asserting backup file exists where we expect it");
		$controller->delete($this_backup);
		$this->ok(!file_exists($this->backup_location . '/' . $this_backup . '.tar.gz'), "asserting backup file has been deleted");
	}
}
