<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Backup controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Backup_Controller extends Ninja_Controller {

	public $debug = false;
	public $model = false;

	private $nagios_cfg_path = "";

	const BACKUP_EXTENSION = '.tar.gz';
	const STORAGE = '/var/www/html/backup/';

	const CMD_UNPACK = 'tar tfz';
	const CMD_VERIFY = '/usr/bin/asmonitor -q /usr/bin/naemon -v';
	const CMD_BACKUP = '/usr/bin/asmonitor -q /opt/monitor/op5/backup/backup';
	const CMD_RESTORE = '/usr/bin/asmonitor -q /opt/monitor/op5/backup/restore';

	public function __construct ()
	{

		parent::__construct();

		$this->nagios_cfg_path = System_Model::get_nagios_etc_path();

	}

	/**
	 * Index page, display backup list with actions
	 *
	 * @return
	 */
	public function index ()
	{

		$this->_verify_access('monitor.system.backup:read.backup');


		$this->template->title = _('Configuration » Backup/Restore');
		$this->template->content = $this->add_view('backup/list');
		$this->template->disable_refresh = true;
		$this->template->js[] = $this->add_path('backup/js/backup.js');

		$files = false;
		foreach (glob(self::STORAGE . '*' . self::BACKUP_EXTENSION) as $filename) {
			$files[] = basename($filename);
		}

		$link = '<a id="verify" href="%sindex.php/backup/verify/">%s %s</a>';
		$icon = '<span style="vertical-align: middle" class="icon-16 x16-backup"></span>';
		$lable = '<span style="vertical-align: middle">' . _('Save your current op5 Monitor configuration') . '</span>';

		$link = sprintf($link, url::base(), $icon, $lable);

		$this->template->toolbar = new Toolbar_Controller(_( "Backup/Restore" ));
		$this->template->toolbar->info($link);

		if ($files === false) {
			$files = array();
			$this->template->content->error = 'Cannot get directory contents: ' . self::STORAGE;
		}

		$this->template->content->files = $files;

	}

	public function view ($file)
	{

		$this->_verify_access('monitor.system.backup:read.backup');

		$this->template->content = $this->add_view('backup/view');
		$this->template->title = _('Configuration » Backup/Restore » View');

		$this->template->content->backup = $file;
		$this->template->disable_refresh = true;

		$this->template->toolbar = new Toolbar_Controller(_( "Backup/Restore" ), $file);

		$this->template->toolbar->info(
			'<a href="' . url::base() . 'index.php/backup" title="' . _( "Backup/Restore" ) . '">' . _( "Backup/Restore List" ) . '</a>'
		);

		list($status, $output) = $this->execute(self::CMD_UNPACK, self::STORAGE . $file);
		sort($output);

		$this->template->content->files = $output;
	}

	/* below are AJAX/JSON actions */

	private function execute ($command) {

		$arguments = array_slice(func_get_args(), 1);
		foreach ($arguments as $index => $value) {
			if (is_array($value)) {
				array_splice($arguments, $index, 1, $value);
			}
		}

		$executable = $command . ' ' . implode(' ', array_map('escapeshellarg', $arguments));

		exec($executable, $output, $status);
		return array($status, $output);

	}

	public function download ($file) {

		$this->_verify_access('monitor.system.backup:read.backup');

		$file_path = self::STORAGE . $file;
		if ($file_path != realpath($file_path)) {
			json::fail('File could not be located within the designated storage area');
		}

		$fp = fopen($file_path, "r");

		if ($fp === false) {
			$this->template->content = $this->add_view('backup/view');
			$this->template->message = "Couldn't create filehandle.";
			return;
		}

		/* Prevent buffering and rendering */
		$this->auto_render = false;
		download::headers($file, filesize($file_path));

		fpassthru($fp);
		fclose($fp);

	}

	public function verify ()
	{

		$this->_verify_access('monitor.system.backup:read.backup');

		list($status, $output) = $this->execute(self::CMD_VERIFY, $this->nagios_cfg_path . 'nagios.cfg');

		if ($status != 0) {
			json::fail(array(
				"message" => "The current configuration is invalid",
				"debug" => $output
			));
		} else {
			json::ok("The current configuration is valid");
		}

	}

	public function backup ()
	{

		$this->_verify_access('monitor.system.backup:create.backup');

		$general_files = array(
			System_Model::get_nagios_etc_path().'nagios.cfg',
			System_Model::get_nagios_etc_path().'cgi.cfg',
			System_Model::get_nagios_base_path().'/var/*.log',
			System_Model::get_nagios_base_path().'/var/status.sav',
			System_Model::get_nagios_base_path().'/var/archives', # Isn't this a config backup?
			System_Model::get_nagios_base_path().'/var/errors',   # Then why would we want these?
			System_Model::get_nagios_base_path().'/var/traffic',
			'/etc/op5/*.yml' # :TODO Read value from op5config
		);

		$backup = array();
		foreach ($general_files as $path) {
			foreach (glob($path) as $file) {
				$backup[] = $file;
			}
		}

		$system = System_Model::parse_config_file($this->nagios_cfg_path. 'nagios.cfg');
		$searches = array('cfg_file', 'resource_file', 'cfg_dir');
		$result = array();

		foreach ($searches as $item) {

			if (!isset($system[$item])) {
				continue;
			} else {
				$files = (is_array($system[$item])) ? $system[$item] : array($system[$item]);
			}

			foreach ($files as $file) {
				$file = ($file[0] !== '/') ? $this->nagios_cfg_path . $file : $file;
				$backup[] = $file;
			}

		}

		$this->auto_render = true;

		$file = strftime('backup-%Y-%m-%d_%H.%M.%S') . self::BACKUP_EXTENSION;

		list($status, $output) = $this->execute(self::CMD_BACKUP, self::STORAGE . $file, $backup);

		if ($status != 0) {
			json::fail(array(
				"message" => "Could not backup the current configuration",
				"debug" => $output
			));
		} else {
			json::ok($file);
		}
	}

	public function restore ($file)
	{

		$this->_verify_access('monitor.system.backup:read.backup');
		$this->_verify_access('monitor.monitoring.hosts:update.backup');
		$this->_verify_access('monitor.monitoring.services:update.backup');
		$this->_verify_access('monitor.monitoring.contacts:update.backup');
		$this->_verify_access('monitor.monitoring.notifications:update.backup');

		list($status, $restore_output) = $this->execute(self::CMD_RESTORE, self::STORAGE . $file);

		if ($status != 0) {
			json::fail(array(
				"message" => "Could not restore the configuration '{$file}'",
				"debug" => $restore_output
			));
		} else {

			list($status, $verify_output) = $this->execute(self::CMD_VERIFY, $this->nagios_cfg_path. 'nagios.cfg');
			if ($status != 0) {
				json::fail(array(
					"message" => "The configuration '{$file}' has been restored but seems to be invalid",
					"debug" => $verify_output
				));
			}

			json::ok("The configuration '{$file}' has been restored successfully");

		}

	}

	public function delete ($file)
	{

		$this->_verify_access('monitor.system.backup:delete.backup');

		$status = @unlink(self::STORAGE . $file);

		if ($status) {
			json::ok("The backup '{$file}' has been deleted");
		} else {
			json::fail(array(
				"message" => "Could not delete the backup '{$file}'",
				"debug" => array("Could not unlink file.")
			));
		}

	}

	public function restart () {

		$user = Auth::instance()->get_user();
		if ($user->authorized_for('system_commands')) {
			$success = nagioscmd::submit_to_nagios('RESTART_PROCESS', "", $output);
			if ($success) {
				json::ok("Restarting monitoring process");
			} else {
				json::fail(array(
					"message" => "Could not restart monitoring process",
					"debug" => $output
				));
			}
		} else {
			json::fail(array(
				"message" => "Not authorized to perform a process restart",
				"debug" => array()
			));
		}

	}

}
