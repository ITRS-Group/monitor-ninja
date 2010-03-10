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

	public function __construct()
	{
		parent::__construct();
		$this->template->content = $this->add_view('backup/list');
		$this->template->disable_refresh = true;
		$this->template->title = $this->translate->_('Configuration » Backup/Restore');
	}

	public function index()
	{
		$this->template->content->status_msg = '';

		$files = @scandir('/var/www/html/backup');
		if ($files === false)
			throw new Exception('Cannot get directory contents: /var/www/html/backup');

		$this->template->content->files = $files;
	}
}
