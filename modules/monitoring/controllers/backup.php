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

	/**
	 * Undocumented variable
	 */
	public $debug = false;
	/**
	 * Undocumented variable
	 */
	public $model = false;

	private $nagios_cfg_path = "";

	/**
	 * Extension for backup files
	 */
	const BACKUP_EXTENSION = '.tar.gz';
	/**
	 * Path for where to store the backups
	 */
	const STORAGE = '/var/www/html/backup/';

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
		$this->template->js[] = 'modules/monitoring/views/backup/js/backup.js';

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

	/**
	 * Undocumented method
	 * @param $file
	 */
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

		proc::open(array('tar', 'tfz', self::STORAGE . $file), $output, $stderr, $status);

		if ($status === 0 && is_string($output)) {
			$files = explode("\n", $output);
			sort($files);
			$this->template->content->files = $files;
		} else {
			$this->template->content->error = _("Could not read content of backup");
		}

	}

	/* below are AJAX/JSON actions */
	/**
	 * Undocumented method
	 * @param $file
	 */
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

	/**
	 * Simple facade for proc::open() with preset values
	 */
	private function _verify_naemon_config(&$stdout, &$stderr, &$status) {
		proc::open(array('/usr/bin/asmonitor','-q', '/usr/bin/naemon', '-v', $this->nagios_cfg_path . 'nagios.cfg'), $stdout, $stderr, $status);
	}

	/**
	 * Undocumented method
	 */
	public function verify ()
	{

		$this->_verify_access('monitor.system.backup:read.backup');
		$this->_verify_naemon_config($stdout, $stderr, $status);
		if ($status) {
			json::fail(array(
				"message" => "The current configuration is invalid",
				"debug" => $stdout
			));
		}
		json::ok("The current configuration is valid");

	}

	/**
	 * Undocumented method
	 */
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
			'/etc/op5/*.yml'
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
			}
			foreach ((array) $system[$item] as $file) {
				$file = ($file[0] !== '/') ? $this->nagios_cfg_path . $file : $file;
				$backup[] = $file;
			}
		}

		$this->auto_render = true;

		$file = strftime('backup-%Y-%m-%d_%H.%M.%S') . self::BACKUP_EXTENSION;

		$command_line = array_merge(array('/usr/bin/asmonitor', '-q', '/opt/monitor/op5/backup/backup', self::STORAGE . $file), $backup);
		proc::open($command_line, $stdout, $stderr, $status);

		if ($status) {
			json::fail(array(
				"message" => "Could not backup the current configuration",
				"debug" => $stderr
			));
		}
		json::ok($file);
	}

	/**
	 * Undocumented method
	 */
	public function restore ($file)
	{

		$this->_verify_access('monitor.system.backup:read.backup');
		$this->_verify_access('monitor.monitoring.hosts:update.backup');
		$this->_verify_access('monitor.monitoring.services:update.backup');
		$this->_verify_access('monitor.monitoring.contacts:update.backup');
		$this->_verify_access('monitor.monitoring.notifications:update.backup');

		proc::open(array('/usr/bin/asmonitor', '-q', '/opt/monitor/op5/backup/restore', self::STORAGE . $file), $stdout, $stderr, $status);

		if ($status) {
			json::fail(array(
				"message" => "Could not restore the configuration '{$file}'",
				"debug" => $stderr
			));
		}

		$this->_verify_naemon_config($stdout, $stderr, $status);
		if ($status) {
			json::fail(array(
				"message" => "The configuration '{$file}' has been restored but seems to be invalid",
				"debug" => $stdout // Naemon writes errors to stdout..
			));
		}

		json::ok("The configuration '{$file}' has been restored successfully");

	}

	/**
	 * Undocumented method
	 */
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

	/**
	 * Undocumented method
	 */
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
