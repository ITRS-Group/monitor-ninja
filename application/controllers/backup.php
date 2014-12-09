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

	private $files2backup;
	private $asmonitor = '/usr/bin/asmonitor -q ';
	private $cmd_backup = '/opt/monitor/op5/backup/backup ';
	private $cmd_restore = '/opt/monitor/op5/backup/restore ';
	private $cmd_view = 'tar tfz ';
	private $cmd_verify;

	private $backup_suffix = '.tar.gz';
	private $backups_location = '/var/www/html/backup';

	private $unauthorized = false;

	public function index()
	{
		$this->_verify_access('system.backup:read');
		$this->template->content = $this->add_view('backup/list');
		$this->template->title = _('Configuration » Backup/Restore');
		$this->template->content->suffix = $this->backup_suffix;

		$backupfiles = false;
		foreach (glob($this->backups_location.'/*'.$this->backup_suffix) as $filename) {
			$backupfiles[] = basename($filename);
		}

		if ($backupfiles === false)
			throw new Exception('Cannot get directory contents: ' . $this->backups_location);


		$link = '<a id="verify" href="' . url::base() . 'index.php/backup/verify/">%s %s</a>';
		$link = sprintf( $link,
			html::image( $this->add_path('/icons/16x16/backup.png'), array('alt' => _('Save your current Monitor configuration'), 'title' => _('Save your current Monitor configuration'), 'style' => 'margin-bottom: -3px')),
			_('Save your current op5 Monitor configuration')
		);

		$this->template->toolbar = new Toolbar_Controller( _( "Backup/Restore" ) );
		$this->template->toolbar->info( $link );

		$this->template->content->files = $backupfiles;
	}

	public function download($file) {
		$this->_verify_access('system.backup:read');
		$file_path = $this->backups_location . "/" . $file;
		$fp = fopen($file_path, "r");
		if ($fp === false) {
			$this->template->content = $this->add_view('backup/view');
			$this->template->message = "Couldn't create filehandle.";
			return;
		}
		/* Prevent buffering and rendering */
		$this->auto_render = false;
		download::headers($file.".tar.gz", filesize($file_path));
		fpassthru($fp);
		fclose($fp);
	}

	public function view($file)
	{
		$this->_verify_access('system.backup:read');

		$this->template->content = $this->add_view('backup/view');
		$this->template->title = _('Configuration » Backup/Restore » View');
		$this->template->content->backup = $file;

		$this->template->toolbar = new Toolbar_Controller( _( "Backup/Restore" ), $file );

		$this->template->toolbar->info(
			'<a href="' . url::base() . 'index.php/backup" title="' . _( "Backup/Restore" ) . '">' . _( "Backup/Restore List" ) . '</a>'
		);

		$contents = array();
		$status = 0;
		exec($this->cmd_view . $this->backups_location . '/' . $file, $contents, $status);
		sort($contents);

		$this->template->content->files = $contents;
	}

	public function verify()
	{
		$this->_verify_access('system.backup:read');

		$this->template = $this->add_view('backup/verify');

		$output = array();
		exec($this->asmonitor . $this->cmd_verify, $output, $status);
		if ($status != 0)
		{
			$this->template->status = false;
			$this->template->message = "The current configuration is invalid";
			$this->debug = implode("\n", $output);
		}
		else
		{
			$this->template->status = true;
			$this->template->message = "The current configuration is valid. Creating a backup...";
		}
	}

	public function backup()
	{
		$this->_verify_access('system.backup:create');

		$nagioscfg = System_Model::get_nagios_etc_path()."nagios.cfg";
		$this->cmd_verify = '/opt/monitor/bin/nagios -v '.$nagioscfg;
		$this->files2backup = array(
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
		foreach ($this->files2backup as $path) {
			foreach (glob($path) as $file) {
				$backup[] = $file;
			}
		}
		$this->files2backup = $backup;

		$this->template->disable_refresh = true;
		$this->auto_render = true;
		$this->cmd_reload = 'echo "[{TIME}] RESTART_PROGRAM" >> ' . System_Model::get_pipe();

		$nagcfg = System_Model::parse_config_file($nagioscfg);
		foreach (array('cfg_file', 'resource_file', 'cfg_dir') as $interesting_file) {
			if (!isset($nagcfg[$interesting_file]))
				continue;
			$files = $nagcfg[$interesting_file];
			if (!is_array($files))
				$files = array($files);
			foreach ($files as $file) {
				if ($file[0] !== '/')
					$file = System_Model::get_nagios_etc_path().$file;
				$this->files2backup[] = $file;
			}
		}

		$this->template = $this->add_view('backup/backup');

		$file = strftime('backup-%Y-%m-%d_%H.%M.%S');
		exec($this->asmonitor . $this->cmd_backup . $this->backups_location . '/' . $file . $this->backup_suffix
			. ' ' . implode(' ', $this->files2backup), $output, $status);
		if ($status != 0)
		{
			$this->template->status = false;
			$this->template->file = '';
			$this->template->message = "Could not backup the current configuration";
			$this->debug = implode("\n", $output);
		}
		else
		{
			$this->template->status = true;
			$this->template->file = $file;
			$this->template->message = "A backup of the current configuration has been created";
		}
	}

	public function restore($file)
	{
		$this->_verify_access('system.backup:read');
		$this->_verify_access('monitoring.hosts:update.backup');
		$this->_verify_access('monitoring.services:update.backup');
		$this->_verify_access('monitoring.contacts:update.backup');
		$this->_verify_access('monitoring.notifications:update.backup');
		$this->_verify_access('monitoring.status:update.backup');
		$this->_verify_access('system.users:update.backup');

		$this->template = $this->add_view('backup/restore');
		$this->template->status = false;

		$status = 0;
		$output = array();
		exec($this->asmonitor . $this->cmd_restore . $this->backups_location . '/' . $file, $output, $status);
		if ($status != 0)
		{
			$this->template->message = "Could not restore the configuration '{$file}'";
			$this->debug = implode("\n", $output);
			return;
		}

		exec($this->asmonitor . $this->cmd_verify, $output, $status);
		if ($status != 0)
		{
			$this->template->message = "The configuration '{$file}' has been restored but seems to be invalid";
			$this->debug = implode("\n", $output);
			return;
		}

		$time = time();
		$this->cmd_reload = str_replace('{TIME}', $time , $this->cmd_reload);

		exec($this->cmd_reload, $output, $status);
		if ($status != 0) {
			$this->template->message = "Could not reload the configuration '{$file}'";
			$this->debug = implode("\n", $output);
		}
		else
		{
			$this->template->status = true;
			$this->template->message = "The configuration '{$file}' has been restored";
			foreach($this->files2backup as $onefile){
				$onefile = trim($onefile);
				if(pathinfo($onefile, PATHINFO_EXTENSION) === "cfg") {
					if(file_exists($onefile) && is_writable($onefile)) {
						touch($onefile);
					}
				}
			}
		}
		return;
	}

	public function delete($file)
	{
		$this->_verify_access('system.backup:delete');

		$this->template = $this->add_view('backup/delete');

		$this->template->status = @unlink($this->backups_location . '/' . $file);
		$this->template->message = $this->template->status ? "The backup '{$file}' has been deleted"
			: "Could not delete the backup '{$file}'";
	}
}
