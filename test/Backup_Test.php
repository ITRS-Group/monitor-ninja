<?php
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\TextUI\Configuration\Group;
/**
 * Test the backup functions that are reached through the GUI. There are other
 * types of backups being run too, you can read more about backups here:
 * https://docs.itrsgroup.com/docs/op5-monitor/
 * Search for "backup" under OP5 monitor
 */
class Backup_Test extends \PHPUnit\Framework\TestCase {

	private $controller;
	private $backup_location;

	public function setUp(): void {
		exec("id monitor", $output, $exit_code);
		if($exit_code != 0) {
			$this->markTestSkipped("Could not find the ".
				"required 'monitor' user");
			return;
		}

		$this->backup_location =  __DIR__.'/a_temp_dir_heyo';
		exec("mkdir $this->backup_location", $output, $exit_code);
		if($exit_code != 0) {
			$this->markTestSkipped("Could not create a temporary ".
				"directory: ".implode("", $output));
			return;
		}

		// all backups are to be owned by the monitor user
		exec("chown monitor $this->backup_location", $output, $exit_code);
		if($exit_code != 0) {
			$this->markTestSkipped("Could not chown the ".
				"temporary directory");
			return;
		}

		Auth::instance(array('session_key' => false))->force_user(new User_AlwaysAuth_Model());
		$this->controller = new Backup_Controller($this->backup_location);
	}

	public function tearDown(): void {
		// php's rmdir() expects an empty directory, be convenient and
		// use the good ol' rm -rf instead, after a small sanity check
		// of the directory's path (yeah, I know, if-cases in tests are
		// evil, so please enlighten me with a proper way to do this
		// in your next commit :))
		if(is_dir($this->backup_location) &&
			preg_match('~^'.__DIR__.'.+~', $this->backup_location)) {
			exec("rm -rf $this->backup_location", $output, $exit_code);
		}
		unset($this->controller);
	}

	public function test_backup() {
		$controller = $this->controller;
		$controller->backup();
		$export_message = var_export($controller->template->message, true);
		$this->assertSame(true, $controller->template->success, $export_message);

		$backups = glob($this->backup_location.'/*');
		$backups_message = "Should have a single backup and nothing else, but we have this:\n".var_export($backups, true);
		$this->assertCount(
			1,
			$backups,
			$backups_message
		);
		$path_info = pathinfo(reset($backups), PATHINFO_BASENAME);
		$template_result = $controller->template->value["result"];
		$this->assertSame(
			$path_info,
			$template_result,
			"The filename in the controller's response should ".
			"match the one that was stored on disk"
		);
	}

	/**
	 * This test technically affects your running configuration, and
	 * needs to be run on the same system as Naemon. The configuration
	 * will be replaced with itself though, if everything goes alright.
	 * If you got a better idea on how to mock these things, but still
	 * test the core, feel free to push some commits :)
	 */
	#[Group('nonlocal')]
	#[Depends('test_backup')]
	public function test_backup_restore() {
		if(!is_executable('/opt/monitor/op5/backup/restore')) {
			$this->markTestSkipped("Need access to the restoring ".
				"script in order to test it");
		} else {
			echo "Restore script is executable.\n";
		}

		$controller = $this->controller;
		$controller->backup();
		$this_backup = $controller->template->value["result"];
		$this->assertFileExists(
			$this->backup_location.'/'.$this_backup,
			"The sanity check of the backup's existence failed"
		);

		// make sure that we're not reusing the old view.. return
		// variables are nice in that they do not rely on state..
		$controller->template = null;
		echo"Before Restore:";
		var_dump($controller->template);
		$controller->restore($this_backup);
		echo"After Restore:";
		var_dump($controller->template);
		$this->assertTrue(
			$controller->template->success,
			var_export($controller->template->message, true)
		);
		$this->assertSame(
			"The configuration '$this_backup' has been restored ".
			"successfully",
			$controller->template->value["result"]
		);
	}

	#[Depends('test_backup')]
	public function test_backup_delete() {
		$controller = $this->controller;
		$controller->backup();
		$this_backup = $controller->template->value["result"];

		$complete_filename = $this->backup_location.'/'.$this_backup;

		$this->assertFileExists(
			$complete_filename,
			"The sanity check of the backup's existence failed"
		);
		$controller->delete($this_backup);
		$this->assertFileDoesNotExist(
			$complete_filename,
			"The file should no longer exist"
		);
	}
}
