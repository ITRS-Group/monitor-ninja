<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Type of authentication method
 * Today only 'file' and 'db' but future version could include
 * LDAP, OpenID and others
 */
$config['driver'] = 'Ninja'; // db

/**
 * Type of hash to use for passwords. Any algorithm supported by the hash function
 * can be used here.
 * @see http://php.net/hash
 * @see http://php.net/hash_algos
 */
$config['hash_method'] = 'sha1';

/**
 * Defines the hash offsets to insert the salt at. The password hash length
 * will be increased by the total number of offsets.
 */
$config['salt_pattern'] = '1, 3, 5, 9, 14, 15, 20, 21, 28, 30';

/**
 * Set the session key that will be used to check if user is logged in
 */
$config['session_key'] = 'auth_user';

/**
 * Max allowed login attempts
 * Set to false to ignore
 */
$config['max_attempts'] = false;

/**
 * Set the auto-login (remember me) cookie lifetime, in seconds. The default
 * lifetime is two weeks.
 */
$config['lifetime'] = 1209600;

/**
 * Usernames (keys) and hashed passwords (values) used by the File driver.
 * Default admin password is "admin". You are encouraged to change this.
 */
$config['users'] = array
(
	'admin' => 'd0bcecba632cad83350fce159fe23cd8ed4fa897b910ac6bd6'
);

$config['min_username_chars'] = 2;

/**
 * Control the use of login by passing username and password
 * as get parameters.
 * Default is set to false and by changing this to true it is
 * possible to login to your account with a url like
 * http://ninja/default/do_login?username=<username>&password=<password>
 */
$config['use_get_auth'] = false;
