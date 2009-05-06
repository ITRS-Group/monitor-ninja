<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * CMD controller
 *
 * Requires authentication. See the helper nagioscmd for more info.
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Cmd_Controller extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Write a raw, pre-formatted, command to Nagios' FIFO
	 * This is a stub for now.
	 *
	 * @param $command The command to write
	 * @return true on success, false on errors
	 */
	private function submit_command($command)
	{
		return false;
	}

	/**
	 * Display "You're not authorized" message
	 */
	public function unauthorized()
	{
		$this->template->content = $this->add_view('cmd/unauthorized');
		$this->template->content->error_message = $this->translate->_('You are not authorized to submit the specified command.');
		$this->template->content->error_description = $this->translate->_('Read the section of the documentation that deals with authentication and authorization in the CGIs for more information.');
		$this->template->content->return_link_lable = $this->translate->_('Return from whence you came');
	}

	/**
	 * Show info to user when use_authentication is disabled in cgi.cfg.
	 */
	public function use_authentication_off()
	{
		$this->template->content = $this->add_view('cmd/use_authentication_off');
		$this->template->content->error_msg = $this->translate->_('Error: Authentication is not enabled!');
		$this->template->content->error_description = $this->translate->_("As a safety precaution, commands aren't allowed when authentication is turned off.");
	}
}
