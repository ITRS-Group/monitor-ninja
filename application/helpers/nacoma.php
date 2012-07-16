<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Helper class for nacoma
 *
 * Copyright 2009 op5 AB
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
	public static function link($path=false, $img=false, $title=false)
	{
		# don't try this if user isn't logged in
		if (!Auth::instance()->logged_in()) {
			return null;
		}
		if (!Auth::instance()->authorized_for('configuration_information') || Kohana::config('config.nacoma_path')===false) {
			return false;
		}
		# create the link.
		$link = false;
		if (!empty($path) && !empty($img)) {
			$link = html::anchor($path, html::image(ninja::add_path($img),array('alt' => $title, 'title' => $title)), array('style' => 'border: 0px'));
		} else {
			# helper only used to decide if the link should be displayed at all
			$link = true;
		}
		return $link;
	}

	/**
	*	Check if the current user is allowed to use Nacoma
	*
	*	@return true/false
	*/
	public static function allowed() {
		if (!Auth::instance()->logged_in()) {
			return null;
		}

		if (!Auth::instance()->authorized_for('configuration_information') || Kohana::config('config.nacoma_path')===false) {
			return false;
		}

		return true;
	}

	/**
	*	Delete host (and associated services) using monitor CLI api
	*
	*	@param $host string host to be deleted
	*/
	public function delHost ($host) {
		if (!Nacoma::allowed()) {
			return false;
		}
		$out = @system('php /opt/monitor/op5/nacoma/api/monitor.php -u ' . Auth::instance()->get_user()->username . ' -t host -n "' . $host . '" -a delete -u >/dev/null', $retval);
	}

	/**
	*	Delete the service using monitor CLI api
	*
	*	@param $service string service to be deleted, format HOST;SERVICE
	*/
	public function delService ($service) {
		if (!Nacoma::allowed()) {
			return false;
		}
		$out = @system('php /opt/monitor/op5/nacoma/api/monitor.php -u ' . Auth::instance()->get_user()->username . ' -t service -n "' . $service . '" -a delete -u >/dev/null', $retval);
	}
}
