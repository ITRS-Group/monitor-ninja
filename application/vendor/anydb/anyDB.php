<?php

////////////////////////////////////////////////////////////////////////

require_once dirname( __FILE__) .'/base/' . 'UtilityClass.php';
require_once dirname( __FILE__) .'/base/' . 'BasicDB.php';

////////////////////////////////////////////////////////////////////////
/**
* Factory class for anyDB
*
* This class provides the different db layers.
*
* Don't instanciate this class.<br>
* Use 'anyDB::methodName()' instead.
*
* @link http://www.phpclasses.org/abstractdb Visit www.phpclasses.org for the latest version
* @author	    Lennart Groetzbach <lennartg@web.de>
* @copyright	Lennart Groetzbach <lennartg@web.de> - distributed under the LGPL
*
* @package      anydb
* @access       public
* @version      31/07/04
*/
////////////////////////////////////////////////////////////////////////
/*
    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
////////////////////////////////////////////////////////////////////////

class anyDB extends UtilityClass {

////////////////////////////////////////////////////////////////////////
/**
* Creates a new db layer
*
* @abstract
* @access   public
*
* @param    String      $layerType          'ADODB', 'METABASE', 'MYSQL', 'PEAR', PHPLIB'
* @param    String      $libraryPath        path to the abstraction layer
* @param    String      $dbType             for the abstraction layers (e.g. 'ODBC')
* @param    Integer     $preferredResType   the preferred result type
*
* @returns  AbstractDB
*/
function getLayer($layerType, $libraryPath, $dbType, $preferredResType = ANYDB_RES_ASSOC) {
    $path = dirname( __FILE__) .'/dbs/';
    $layer = strtoupper(trim($layerType));
    // checks if php extension is loaded
/*
    if ($dbType != '') {
        anyDB::checkForDB($dbType);
    } else {
        anyDB::checkForDB($layer);
    }
*/
    // gets the layer
    $res = null;
    switch ($layer) {
        case 'ADODB':
			require_once $path . 'ADOdbDB.php';
            $res = new ADOdbDB($libraryPath, $dbType, $preferredResType);
            break;
        case 'METABASE':
			require_once $path . 'MetabaseDB.php';
            $res = new MetabaseDB($libraryPath, $dbType, $preferredResType);
            break;
        case 'MYSQL':
			require_once $path . 'MysqlDB.php';
            $res = new MysqlDB('', 'mysql', $preferredResType);
            break;
        case 'ODBC':
			require_once $path . 'OdbcDB.php';
            $res = new OdbcDB('', $dbType, $preferredResType);
            break;
        case 'PEAR':
			require_once $path . 'PearDB.php';
            $res = new PearDB($libraryPath, $dbType, $preferredResType);
            break;
        case 'PGSQL':
        case 'POSTGRES':
        case 'POSTGRESQL':
			require_once $path . 'PostgresDB.php';
            $res = new PostgresDB('', 'pgsql', $preferredResType);
            break;
        case 'SQLITE':
			require_once $path . 'SQLiteDB.php';
            $res = new SQLiteDB('', 'sqlite', $preferredResType);
            break;
        default:
            die ('Unknown database layer!');
    }
    return $res;
}

////////////////////////////////////////////////////////////////////////
/**
* Get a db layer for an existing db resource
*
* @abstract
* @access   public
*
* @param    String      $dbIdentifier       db resource
* @param    String      $libraryPath        path to the abstraction layer
* @param    Integer     $preferredResType   the preferred result type
*
* @returns  AbstractDB
*/
function wrapDB(& $dbIdentifier, $libraryPath, $preferredResType = ANYDB_RES_ASSOC) {
    $path = dirname( __FILE__) .'/dbs/';
    $res = null;

    // adodb
    if (is_subclass_of($dbIdentifier, 'ADOConnection')) {
		require_once $path . 'ADOdbDB.php';
        $res = new ADOdbDB($libraryPath, $dbIdentifier->databaseType, $preferredResType);
        $res->db = $dbIdentifier;
        $res->database = $dbIdentifier->database;
        $res->host = $dbIdentifier->host;
        $res->password = $dbIdentifier->password;
        $res->user = $dbIdentifier->user;

    // pear
    } else if (is_subclass_of($dbIdentifier, 'DB_common')) {
		require_once $path . 'PearDB.php';
        $res = new PearDB($libraryPath, $dbIdentifier->phptype, $preferredResType);
        $res->db = $dbIdentifier;
        $res->database = $dbIdentifier->_db;
        $res->host = $dbIdentifier->dsn['hostspec'];
        $res->user = $dbIdentifier->dsn['username'];
        $res->password = $dbIdentifier->dsn['password'];

    // mysql
    } else if (@get_resource_type($dbIdentifier) == 'mysql link') {
		require_once $path . 'MysqlDB.php';
        $res = new MySqlDB('', 'mysql', $preferredResType);
        $res->db = $dbIdentifier;

// UNTESTED!
    // sqlite
    } else if (@get_resource_type($dbIdentifier) == 'sqlite database') {
		require_once $path . 'SQLiteDB.php';
        $res = new MySqlDB('', 'sqlite', $preferredResType);
        $res->db = $dbIdentifier;

// UNTESTED!
    // pgsql
    } else if (@get_resource_type($dbIdentifier) == 'pgsql') {
		require_once $path . 'PostgresDB.php';
        $res = new PostgresDB('', 'pgsql', $preferredResType);
        $res->db = $dbIdentifier;

    // phplib
    } else if (is_subclass_of($dbIdentifier, 'DB_Sql')) {
        require_once $path . 'phplib/myDB_Sql.php';
		require_once $path . 'PhplibDB.php';
        $res = new PhplibDB($libraryPath, $dbIdentifier->type, $preferredResType);

        $res->db = new myDB_Sql($dbIdentifier->Host, $dbIdentifier->Database, $dbIdentifier->User, $dbIdentifier->Password);
        $res->database = $dbIdentifier->Database;
        $res->host = $dbIdentifier->Host;
        $res->user = $dbIdentifier->User;
        $res->password = $dbIdentifier->Password;

    // metabase
    }else if (is_int($dbIdentifier)) {
        global $metabase_databases;
        if (isset($metabase_databases[$dbIdentifier])) {
            // detect db type
            if (is_a($metabase_databases[$dbIdentifier], 'metabase_mysql_class')) {
                $type = 'mysql';
            } else if (is_a($metabase_databases[$dbIdentifier], 'metabase_ifx_class')) {
                $type = 'ifx';
            } else if (is_a($metabase_databases[$dbIdentifier], 'metabase_msql_class')) {
                $type = 'msql';
            }  else if (is_a($metabase_databases[$dbIdentifier], 'metabase_mssql_class')) {
                $type = 'mssql';
            }  else if (is_a($metabase_databases[$dbIdentifier], 'metabase_oci_class')) {
                $type = 'oci';
            } else if (is_a($metabase_databases[$dbIdentifier], 'metabase_odbc_class')) {
                $type = 'odbc';
            } else if (is_a($metabase_databases[$dbIdentifier], 'metabase_pgsql_class')) {
                $type = 'pgsql';
            } else {
                die ('Unknown metabase db type!');
            }

		require_once $path . 'MetabaseDB.php';
            $res = new MetabaseDB($libraryPath, $type, $preferredResType);
            $res->db = $dbIdentifier;
            $res->database = $metabase_databases[$dbIdentifier]['database_name'];
            $res->host = $metabase_databases[$dbIdentifier]['host'];
            $res->user = $metabase_databases[$dbIdentifier]['user'];
            $res->password = $metabase_databases[$dbIdentifier]['password'];
        }

    // error
    } else {
        echo "Unknown identifier!";
    }
    return $res;
}
////////////////////////////////////////////////////////////////////////
/**
* Checks if php extension for this db type is loaded
*
* @abstract
* @access   public
*
* @param    String      $dbType         db type
*
* @returns  AbstractDB
*/
function checkForDB($dbType) {
    switch (strtolower($dbType)) {
    // dbx
        case 'dbx':       // dbx
            return;

    // sqlite
        case 'sqlite':       // dbx
            if (extension_loaded('sqlite')) {
                return;
            }
            break;

    // mysql
        case 'mysql':       // phplib // metabase // pear // adodb
        case 'mysqll':      // adodb
        case 'mysqlt':      // adodb
            if (extension_loaded('mysql')) {
                return;
            }
            break;

    // postgresql
        case 'pgsql':       // phplib // metabase // pear
        case 'postgres':    // ado
        case 'postgres64':  // ado
        case 'postgres7':   // ado
            if (extension_loaded('pgsql')) {
                return;
            }
            break;

    // oracle 8
        case 'oci':         // metabase
        case 'oci8':        // ado // pear // phplib
        case 'oci8po':      // ado
            if (extension_loaded('mysql')) {
                return;
            }
            break;
    // odbc
        case 'odbc':  // adodb
        case 'odbc_mssql':  // adodb
        case 'odbc_oracle': // ado
            if (extension_loaded('odbc')) {
                return;
            }
            break;
    // oracle
        case 'oracle':      // phplib // ado
            if (extension_loaded('oracle')) {
                return;
            }
            break;
    // microsoft sql
        case 'mssql':       // metabase // pear // phplib // adodb
        case 'mssqlpo':     // adodb
            if (extension_loaded('mssql')) {
                return;
            }
            break;
    // mini sql
        case 'msql':        // metabase // pear // phplib
            if (extension_loaded('mssql')) {
                return;
            }
            break;
    // informix
        case 'ifx':         // metabase // pear
        case 'informix':    // adodb
            break;
    // sybase
        case 'sybase':      // pear // phplib // adodb
            if (extension_loaded('sybase')) {
                return;
            }
            break;
    // frontbase
        case 'frontbase':   // pear
        case 'fbsql':       // adodb
            return;
            break;
    // interbase
        case 'ibase':       // metabase // pear // adodb
            if (extension_loaded('interbase')) {
            return;
                return;
            }
            break;
    }
    die("PHP extension not loaded for '$dbType'!");
}
////////////////////////////////////////////////////////////////////////

}
////////////////////////////////////////////////////////////////////////
?>