<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Helper class for alert log
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
class alertlog_Core {
	/**
	 * Convert all sorts of constants to user-readable strings, add html, and generally make things pretty
	 * @param $entry A database row
	 * @return An array, somewhat similar to the entry one, but with new values
	 */
	public static function get_user_friendly_representation($entry) {
		$ret = array(
			'type' => '',
			'obj_name' => '',
			'state' => '',
			'image' => '',
			'softorhard' => '',
			'retry' => ''
		);
		switch ($entry->event_type) {
		 case 100:
			$ret['type'] = 'PROCESS START';
			$ret['state'] = "Start";
			$ret['image'] = html::image(ninja::add_path('icons/16x16/'.strtolower($ret['state']).'.png'), array('alt' => _($ret['state']), 'title' => _($ret['state'])));
			break;
		 case 102:
			$ret['type'] = 'PROCESS RESTART';
			$ret['state'] = "Restart";
			$ret['image'] = html::image(ninja::add_path('icons/16x16/'.strtolower($ret['state']).'.gif'), array('alt' => _($ret['state']), 'title' => _($ret['state'])));
			break;
		 case 103:
			$ret['type'] = 'PROCESS SHUTDOWN';
			$ret['state'] = 'Stop';
			$ret['image'] = html::image(ninja::add_path('icons/16x16/'.strtolower($ret['state']).'.png'), array('alt' => _($ret['state']), 'title' => _($ret['state'])));
			break;
		 case 701:
			$ret['type'] = 'SERVICE ALERT';
			switch ($entry->state) {
			 case 0:
				$ret['state'] = 'OK';
				break;
			 case 1:
				$ret['state'] = 'Warning';
				break;
			 case 2:
				$ret['state'] = 'Critical';
				break;
			 case 3:
				$ret['state'] = 'Unknown';
				break;
			 default:
				# technically, "unknown unknown, as opposed to known unknown above"
				$ret['state'] = 'Pending';
				break;
			}
			$ret['image'] = html::image(ninja::add_path('icons/16x16/shield-'.strtolower($ret['state']).'.png'), array('alt' => _($ret['state']), 'title' => _($ret['state'])));
			$ret['softorhard'] = $entry->hard ? 'HARD' : 'SOFT';
			break;
		 case 801:
			$ret['type'] = 'HOST ALERT';
			switch ($entry->state) {
			 case 0:
				$ret['state'] = 'Up';
				break;
			 case 1:
				$ret['state'] = 'Down';
				break;
			 case 2:
				$ret['state'] = 'Unreachable';
				break;
			 default:
				$ret['state'] = 'Pending';
				break;
			}
			$ret['image'] = html::image(ninja::add_path('icons/16x16/shield-'.strtolower($ret['state']).'.png'), array('alt' => _($ret['state']), 'title' => _($ret['state'])));
			$ret['softorhard'] = $entry->hard ? 'HARD' : 'SOFT';
			break;
		 case 1103:
		 case 1104:
			if ($entry->service_description)
				$ret['type'] = 'SERVICE DOWNTIME ALERT';
			else
				$ret['type'] = 'HOST DOWNTIME ALERT';
			$ret['state'] = $entry->event_type == 1103 ? 'Started' : 'Stopped';
			$ret['image'] = html::image(ninja::add_path('icons/16x16/scheduled-downtime.png'), array('alt' => _('Scheduled downtime'), 'title' => _('Scheduled downtime')));
			break;
		 default:
			$ret['type'] = "UNKNOWN EVENT #$event->entry_type";
			break;
		}

		$ret['obj_name'] = $entry->host_name;
		if ($entry->service_description)
			$ret['obj_name'] = html::anchor('extinfo/details/service/'.urlencode($ret['obj_name']).'?service='.urlencode($entry->service_description), $ret['obj_name'].';'.$entry->service_description);
		elseif ($entry->host_name)
			$ret['obj_name'] = html::anchor('extinfo/details/host/'.urlencode($ret['obj_name']), $ret['obj_name']);
		else
			$ret['obj_name'] = false;

		$ret['retry'] = $entry->retry == '0' ? '' : $entry->retry;

		return $ret;
	}
}
