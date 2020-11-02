<?php
/**
 * If a user is not logged in but attempts to access a page that does not
 * exist, don't display the chrome of the product/actually enter the product.
 * Redirect to login instead.
 */
Event::add('system.404', function () {
	if (Router::$controller !== 'auth') {
		if (!Op5Auth::instance()->get_user()->logged_in()) {
			url::redirect(Kohana::config('routes.log_in_form'));
		}
	}
});
