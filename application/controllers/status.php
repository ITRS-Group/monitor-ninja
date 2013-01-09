<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Status controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Status_Controller extends Authenticated_Controller {

	/**
	 * Equivalent to style=hostdetail
	 *
	 * @param $host
	 */
	public function host($host='all')
	{
		$host = $this->input->get('host', $host);
		if( $host == 'all' ) {
			$query = '[hosts] all';
		} else {
			$query = '[services] host.name = "'.addslashes($host).'"';
		}
		return $this->_redirect_to_query($query);
	}

	/**
	 * List status details for hosts and services
	 *
	 */
	public function service($host='all')
	{
		$host = $this->input->get('host', $host);
		if( $host == 'all' ) {
			$query = '[services] all';
		} else {
			$query = '[services] host.name = "'.addslashes($host).'"';
		}
		return $this->_redirect_to_query($query);
	}

	/**
	*	Wrapper for Service problems link in menu
	*/
	public function service_problems()
	{
		return $this->_redirect_to_query('[services] has_been_checked!=0 and state!=0');
	}

	/**
	*	Wrapper for Host problems link in menu
	*/
	public function host_problems()
	{
		return $this->_redirect_to_query('[hosts] has_been_checked!=0 and state!=0');
	}

	/**
	 * Show servicegroup status, wrapper for group('service', ...)
	 * @param $group
	 *
	 */
	public function servicegroup($group='all')
	{
		$group = $this->input->get('group', $group);
		return $this->_redirect_to_query('[services] in "'.addslashes($group).'"');
	}

	/**
	 * Show hostgroup status, wrapper for group('host', ...)
	 * @param $group
	 *
	 */
	public function hostgroup($group='all')
	{
		$group = $this->input->get('group', $group);
		return $this->_redirect_to_query('[hosts] in "'.addslashes($group).'"');
	}

	/**
	 * Display servicegroup summary
	 */
	public function servicegroup_summary()
	{
		return $this->_redirect_to_query('[servicegroups] all');
	}

	/**
	 * Display hostgroups summary
	 */
	public function hostgroup_summary()
	{
		return $this->_redirect_to_query('[hostgroups] all');
	}
	
	private function _redirect_to_query( $query ) {
		return url::redirect('listview?'.http_build_query(array('q'=>$query)));
	}
}
