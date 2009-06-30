#!/usr/bin/php -q
<?php
/**
 * Import existing authorization settings from cgi.cfg to database
 * for all users.
 */

system('/usr/bin/php ../index.php cli/insert_user_data monitor ', $result);
#print_r($result);

?>