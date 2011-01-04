<?php
/**

 This file/class is largely a kludge/workaround to give PDO access
 to the Ninja DB to some utility PHP code which does not itself use
 Kohana.
*/

if( defined('SYSPATH') ) {
    require(SYSPATH.'/libraries/PDOProvider.php');
}
else {
    require(dirname(__FILE__).'/system/libraries/PDOProvider.php');
}

class NinjaPDO
{
    private static $pdo = NULL;

    public static function db()
    {

        if( NULL != self::$pdo ) return $pdo;
        $c =& PDOProvider::config();
        $c['type'] = 'mysql';
        $c['host'] = 'localhost';
        $c['user'] = 'merlin';
        $c['passwd'] = 'merlin';
        $c['database'] = 'merlin';
        $c['port'] = 3306;
        //print_r( $c );
        self::$pdo = PDOProvider::db()/* set up initial connection. will throw if a connection cannot be established. */;
        return self::$pdo;
    }

};

?>
