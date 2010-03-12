<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Backup controller
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Backup_Controller extends Authenticated_Controller {

	public $model = false;
	
	private $files2backup = array(
		'/opt/monitor/etc/*.cfg',
		'/opt/monitor/etc/*.users',
		'/opt/monitor/var/*.log',
		'/opt/monitor/var/archives',
		'/opt/monitor/var/errors',
		'/opt/monitor/var/traffic',
	);
	
	private $cmd_backup = '/opt/monitor/op5/backup/backup';
	private $cmd_restore = '/opt/monitor/op5/backup/restore ';
	private $cmd_verify = '/opt/monitor/bin/nagios -v /opt/monitor/etc/nagios.cfg 2>/dev/null';
	private $cmd_reload = 'echo "[{TIME}] RESTART_PROGRAM;{TIME2}" >> /opt/monitor/var/rw/nagios.cmd && touch /opt/monitor/etc/misccommands.cfg';
	private $cmd_view = 'tar tfz ';

	private $backup_suffix = '.tar.gz';
	private $backups_location = '/var/www/html/backup';

	public function __construct()
	{
		parent::__construct();
		$this->template->disable_refresh = true;
	}

	public function index()
	{
		$this->template->content = $this->add_view('backup/list');
		$this->template->title = $this->translate->_('Configuration » Backup/Restore');

		$files = @scandir($this->backups_location);
		if ($files === false)
			throw new Exception('Cannot get directory contents: /var/www/html/backup');

		$suffix_len = strlen($this->backup_suffix);
		$backupfiles = array();
		foreach ($files as $file)
			if (substr($file, -$suffix_len) == $this->backup_suffix)
				$backupfiles[] = substr($file, 0, -$suffix_len);

		$this->template->content->files = $backupfiles;
	}

	public function view($file)
	{
		$this->template->content = $this->add_view('backup/view');
		$this->template->title = $this->translate->_('Configuration » Backup/Restore » View');
		$this->template->content->backup = $file;

		$contents = array();
		$status = 0;
		exec($this->cmd_view . $this->backups_location . '/' . $file . $this->backup_suffix . ' 2>/dev/null', $contents, $status);
		sort($contents);

		$this->template->content->files = $contents;
	}

	public function restore($file)
	{
		$this->template = $this->add_view('backup/restore');
		$this->template->status = false;

		$status = 0;
		system($this->cmd_restore . $this->backups_location . '/' . $file . $this->backup_suffix . ' 2>/dev/null', $status);
		if ($status != 0)
		{
			$this->template->message = "Could not restore the configuration '{$file}'";
			return;
		}

		system($this->cmd_verify, $status);
		if ($status != 0)
		{
			$this->template->message = "Could not verify the configuration '{$file}'";
			return;
		}

		$time = time();
		$this->cmd_reload = str_replace('{TIME}', $time , $this->cmd_reload);
		$this->cmd_reload = str_replace('{TIME2}', $time + 2 , $this->cmd_reload);
		system($this->cmd_reload . $this->backups_location . '/' . $file . $this->backup_suffix . ' 2>/dev/null', $status);
		if ($status == 0)
			$this->template->message = "Could not reload the configuration '{$file}'";
		else
		{
			$this->template->status = true;
			$this->template->message = "The configuration '{$file}' has been restored";
		}
	}

	public function delete($file)
	{
		$this->template = $this->add_view('backup/delete');

		$this->template->status = @unlink($this->backups_location . '/' . $file . $this->backup_suffix);
		$this->template->message = $this->template->status ? "The backup '{$file}' has been deleted"
			: "Could not delete the backup '{$file}' has been deleted";
	}
}
