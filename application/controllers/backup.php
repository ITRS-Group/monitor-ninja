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
	
	private $cmd_restore = '/opt/monitor/op5/backup/restore';
	private $cmd_verify = '/opt/monitor/bin/nagios -v /opt/monitor/etc/nagios.cfg';
	private $cmd_reload = 'echo "[$time] RESTART_PROGRAM;$time2" >> /opt/monitor/var/rw/nagios.cmd && touch /opt/monitor/etc/misccommands.cfg';
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
		$this->template->content->status_msg = '';

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
		$this->template->content->status_msg = '';

		$contents = array();
		$status = 0;
		exec($this->cmd_view . $this->backups_location . '/' . $file . $this->backup_suffix . ' 2>/dev/null', $contents, $status);
		sort($contents);

		$this->template->content->files = $contents;
	}
}
