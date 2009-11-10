<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Helper class for nacoma
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
class nacoma_Core {

	/**
	*	Check if a link to Nacoma should be displayed
	*	This depends on if Nacoma is actually available
	* 	and if the user is authorized to use it.
	*/
	public function link($path=false, $img=false, $title=false)
	{
		# don't try this if user isn't logged in
		if (!Auth::instance()->logged_in()) {
			return null;
		}
		$auth = new Nagios_auth_Model();
		if (!$auth->authorized_for_configuration_information || Kohana::config('config.nacoma_path')===false) {
			return false;
		}
		# create the link.
		$link = false;
		if (!empty($path) && !empty($img)) {
			$link = html::anchor($path, html::image($this->add_path($img),$title));
		} else {
			# helper only used to decide if the link should be displayed at all
			$link = true;
		}
		return $link;
	}
}