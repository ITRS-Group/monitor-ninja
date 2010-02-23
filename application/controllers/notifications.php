<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Notifications controller
 * Requires authentication
 *
 * @package	NINJA
 * @author	op5 AB
 * @license	GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Notifications_Controller extends Authenticated_Controller {
	public $current = false;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Default controller method
	 */
	public function index($sort_field='host_name', $sort_order='ASC', $type = '', $query_type = nagstat::FIND_HOST)
	{

		//$items_per_page = urldecode($this->input->get('items_per_page', Kohana::config('pagination.default.items_per_page'))); # @@@FIXME: should be configurable from GUI
		$items_per_page = urldecode($this->input->get('items_per_page', 20)); # @@@FIXME: should be configurable from GUI
		$items_per_page = 100;
		$note_model = new Notifications_Model($items_per_page, true, true);
		$note_model->sort_order = urldecode($this->input->get('sort_order', $sort_order));
		$note_model->sort_field = urldecode($this->input->get('sort_field', $sort_field));

		$note_model->where = urldecode($this->input->get('type', $type));

		$result = $note_model->show_notifications();

		$pagination = new Pagination(
			array(
				'total_items'=> $note_model->count_notifications(),
				'items_per_page' => $items_per_page
			)
		);

		$note_model->offset = $pagination->sql_offset;

		$this->template->title = $this->translate->_('Reporting').' » '.$this->translate->_('Contact Notifications');
		$this->template->content = $this->add_view('notifications/index');
		$this->template->content->data = $result;
		$this->template->content->query_type = $query_type;
		$this->template->content->type = $type;
		$this->template->content->pagination = isset($pagination) ? $pagination : false;
	}
}