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

	private $nagios_cfg_path = "";

	/**
	 * Path for where to store the backups
	 */
	private $backup_directory;

	/**
	 * Extension for backup files
	 */
	const BACKUP_EXTENSION = '.tar.gz';

	/**
	 * @param $backup_directory string, defaults to the value Monitor uses in production
	 */
	public function __construct($backup_directory = '/var/www/html/backup') {
		$this->backup_directory = $backup_directory;
		parent::__construct();
		$this->nagios_cfg_path = System_Model::get_nagios_etc_path();
	}

	/**
	 * List the backup files
	 */
	public function index () {
		$this->_verify_access('monitor.system.backup:read.backup');


		$this->template->title = _('Configuration » Backup/Restore');
		$this->template->content = $this->add_view('backup/list');

		$files = array();
		foreach (glob($this->backup_directory .'/*' . self::BACKUP_EXTENSION) as $filename) {
			$files[] = basename($filename);
		}

		$link = '<a id="verify_backup" href="%sindex.php/backup/verify/">%s %s</a>';
		$icon = '<span style="vertical-align: middle" class="icon-16 x16-backup"></span>';
		$label = '<span style="vertical-align: middle">' . _('Save your current op5 Monitor configuration') . '</span>';

		$link = sprintf($link, url::base(), $icon, $label);

		$this->template->toolbar = new Toolbar_Controller(_( "Backup/Restore" ));
		$this->template->toolbar->info($link);

		$this->template->content->files = $files;
	}

	/**
	 * View the contents of a backup file
	 *
	 * @param $file
	 */
	public function view ($file) {
		$this->_verify_access('monitor.system.backup:read.backup');

		$this->template->content = $this->add_view('backup/view');
		$this->template->title = _('Configuration » Backup/Restore » View');

		$this->template->content->backup = $file;

		$this->template->toolbar = new Toolbar_Controller(_( "Backup/Restore" ), $file);

		$this->template->toolbar->info(
			'<a href="' . url::base() . 'index.php/backup" title="' . _( "Backup/Restore" ) . '">' . _( "Backup/Restore List" ) . '</a>'
		);

		proc::open(array('tar', 'tfz', $this->backup_directory .'/'. $file), $output, $stderr, $status);

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
	 * Download a backup file to the client.
	 *
	 * @param $file string
	 */
	public function download ($file) {
		$this->_verify_access('monitor.system.backup:read.backup');

		$file_path = $this->backup_directory .'/'. $file;
		if ($file_path != realpath($file_path)) {
			$this->template = json::fail_view('File could not be located within the designated storage area');
			return;
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
	 *
	 * @param $stdout
	 * @param $stderr
	 * @param $status
	 */
	private function _verify_naemon_config(&$stdout, &$stderr, &$status) {
		proc::open(array('/usr/bin/asmonitor', '/usr/bin/naemon', '-v', $this->nagios_cfg_path . 'nagios.cfg'), $stdout, $stderr, $status);
	}

	/**
	 * Proxy for checking if the currently running Naemon configuration is
	 * valid. Not sure why we do this, nor why we do it here.
	 */
	public function verify () {
		$this->_verify_access('monitor.system.backup:read.backup');
		$this->_verify_naemon_config($stdout, $stderr, $status);
		if ($status) {
			$this->template = json::fail_view(array(
				"message" => "The current configuration is invalid",
				"debug" => $stdout
			));
			return;
		}
		$this->template = json::ok_view("The current configuration is valid");
	}

	/**
	 * Create a new backup file
	 */
	public function backup () {
		$this->_verify_access('monitor.system.backup:create.backup');

		$general_files = array(
			System_Model::get_nagios_etc_path().'nagios.cfg',
			System_Model::get_nagios_etc_path().'cgi.cfg',
			System_Model::get_nagios_base_path().'/var/*.log',
			System_Model::get_nagios_base_path().'/var/status.sav',
			System_Model::get_nagios_base_path().'/var/archives', # Isn't this a config backup?
			System_Model::get_nagios_base_path().'/var/errors',   # Then why would we want these?
			System_Model::get_nagios_base_path().'/var/traffic',
			'/etc/op5/auth*.yml',
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

		$file = date_format(new DateTime(), 'backup-Y-m-d_H.i.s') . self::BACKUP_EXTENSION;

		$command_line = array_merge(array('/usr/bin/asmonitor', '-q', '/opt/monitor/op5/backup/backup', $this->backup_directory .'/'. $file), $backup);
		proc::open($command_line, $stdout, $stderr, $status);

		if ($status) {
			$this->template = json::fail_view(array(
				"message" => "Could not backup the current configuration",
				"debug" => $stderr
			));
			return;
		}
		$this->template = json::ok_view($file);
	}

	/**
	 * Restores the backup referred to as $file
	 *
	 * @param $file string
	 */
	public function restore ($file) {
		$this->_verify_access('monitor.system.backup:read.backup');
		$this->_verify_access('monitor.monitoring.hosts:update.backup');
		$this->_verify_access('monitor.monitoring.services:update.backup');
		$this->_verify_access('monitor.monitoring.contacts:update.backup');
		$this->_verify_access('monitor.monitoring.notifications:update.backup');

		proc::open(array('/usr/bin/asmonitor', '-q', '/opt/monitor/op5/backup/restore', $this->backup_directory .'/'. $file), $stdout, $stderr, $status);

		if ($status) {
			$this->template = json::fail_view(array(
				"message" => "Could not restore the configuration '{$file}'",
				"debug" => $stderr
			));
			return;
		}

		$this->_verify_naemon_config($stdout, $stderr, $status);
		if ($status) {
			$this->template = json::fail_view(array(
				"message" => "The configuration '{$file}' has been restored but seems to be invalid",
				"debug" => $stdout // Naemon writes errors to stdout..
			));
			return;
		}

		proc::open(array('/usr/bin/asmonitor', '/usr/bin/php', '/opt/monitor/op5/nacoma/api/monitor.php', '-a', 'undo_config'), $stdout, $stderr, $status);

		if ($status) {
			$this->template = json::fail_view(array(
				"message" => "The configuration '{$file}' has been restored, but it could not be loaded into the database.",
				"debug" => $stderr
			));
			return;
		}

		$this->template = json::ok_view("The configuration '{$file}' has been restored successfully");
	}

	/**
	 * Remove an unwanted backup file
	 *
	 * @param $file string
	 */
	public function delete ($file) {
		$this->_verify_access('monitor.system.backup:delete.backup');

		$status = @unlink($this->backup_directory .'/'. $file);

		if ($status) {
			$this->template = json::ok_view("The backup '{$file}' has been deleted");
			return;
		}
		$this->template = json::fail_view(array(
			"message" => "Could not delete the backup '{$file}'",
			"debug" => array("Could not unlink file.")
		));
	}

	/**
	 * In order to reload the Monitor daemon with configuration from a
	 * backup, we need to restart it from within the GUI and report back
	 * how it went.
	 */
	public function restart () {
		$user = Auth::instance()->get_user();
		if (!$user->authorized_for('system_commands')) {
			$this->template = json::fail_view(array(
				"message" => "Not authorized to perform a process restart",
				"debug" => array()
			));
		}
		$success = nagioscmd::submit_to_nagios('RESTART_PROCESS', "", $output);
		if ($success) {
			$this->template = json::ok_view("Restarting monitoring process");
			return;
		}
		$this->template = json::fail_view(array(
			"message" => "Could not restart monitoring process",
			"debug" => $output
		));
	}
}
