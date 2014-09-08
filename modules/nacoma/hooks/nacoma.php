<?php
/**
 * Class encapsulating hooks needed to register links and notifications to
 * integrate with nacoma.
 */
class nacoma_hooks {
	public function __construct() {
		Event::add('system.post_controller_constructor',
			array ($this,'load_notifications'));
	}

	/**
	 * Hook executed in system.post_controller_constructor, to load notifications
	 * regarding nacoma.
	 */
	public function load_notifications() {
		$controller = Event::$data;
		/*
		 * We can only add notifications to the ninja controller, so don't bother
		 * otherwise
		 */
		if ($controller instanceof Ninja_Controller) {
			$user = Auth::instance()->get_user();
			if (nacoma::link() === true &&
				 $user->authorized_for('configuration_information') &&
				 $user->authorized_for('system_commands') &&
				 $user->authorized_for('host_view_all')) {

				$nacoma = Database::instance('nacoma');
				$query = $nacoma->query(
					'SELECT COUNT(id) AS cnt FROM autoscan_results WHERE visibility != 0');
				$query->result(false);
				$row = $query->current();
				if ($row !== false && $row['cnt'] > 0) {
					$controller->add_global_notification(
						html::anchor(
							'configuration/configure?scan=autoscan_complete',
							$row['cnt'] . _(' unmonitored hosts present.')));
				}
			}
		}
	}
}

new nacoma_hooks();