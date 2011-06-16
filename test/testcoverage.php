<?php
xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);

function on_exit() {
	ob_end_clean();
	var_export(xdebug_get_code_coverage());
}

register_shutdown_function('on_exit');

ob_start();
require_once(dirname(__FILE__)."/../index.php");
