<?php defined('SYSPATH') OR die('No direct access allowed.');

# control the use of keycommands in Ninja
$config['activated'] = false;

# set focus to search field
$config['search'] = 'Alt+Shift+f';

# pause/unpause page refresh
$config['pause'] = 'Alt+Shift+p';

# pagination controls
$config['forward'] = 'Alt+Shift+right';
$config['back'] = 'Alt+Shift+left';

# check for custom config files that
# won't be overwritten on upgrade
if (file_exists(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__))) {
	include(realpath(dirname(__FILE__)).'/custom/'.basename(__FILE__));
}
