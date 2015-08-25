<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Toolbox for listview columns and such. Mostly via ajax
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 *
 */
class ListView_Tools_Controller extends Authenticated_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Ajax calls shouldn't be rendered. This doesn't, because some unknown
		 * magic doesn't render templates in ajax requests, but for debugging
		 */
		$this->auto_render = false;
	}

	/**
	*	Fetch comment for object
	*/
	public function fetch_comments()
	{
		$host = $this->input->get('host', false);
		$service = false;

		if (strstr($host, ';')) {
			# we have a service - needs special handling
			$parts = explode(';', $host);
			if (sizeof($parts) == 2) {
				$host = $parts[0];
				$service = $parts[1];
			}
		}

		$data = _('Found no data');
		$set = CommentPool_Model::all();
		/* @var $set ObjectSet_Model */
		$set = $set->reduce_by('host.name', $host, '=');
		if($service !== false)
			$set = $set->reduce_by('service.description', $service, '=');

		if (count($set) > 0) {
			$data = "<table><tr><th>"._("Timestamp")."</th><th>"._('Author')."</th><th>"._('Comment')."</th></tr>";
			foreach ($set->it(array('entry_time', 'author', 'comment'),array()) as $row) {
				$data .= '<tr><td>'.date(date::date_format(), $row->get_entry_time()).'</td><td valign="top">'.html::specialchars($row->get_author()).'</td><td width="400px">'.wordwrap(html::specialchars($row->get_comment()), '50', '<br />').'</td></tr>';
			}
			$data .= '</table>';
		}

		echo $data;
	}
}
