<?php
////////////////////////////////////////////////////////////////////////
/**
* "Interface" for the anyDB
*
* This class defines the public methods and constants for anyDB.
* Don't call or instanciate this class.
*
* @link        http://lensphp.sourceforge.net for the latest version
* @author	   Lennart Groetzbach <lennartg[at]web.de>
* @copyright	Lennart Groetzbach <lennartg[at]web.de> - distributed under the LGPL
*
* @package      anydb
* @abstract
* @access       public
* @version      08/24/04
*/
////////////////////////////////////////////////////////////////////////

define('ANYDB_PREDEFINED_VALUE', -1);

define('ANYDB_RES_ASSOC', 1);
define('ANYDB_RES_NUM', 2);
define('ANYDB_RES_BOTH', ANYDB_RES_NUM + ANYDB_RES_ASSOC);
define('ANYDB_RES_OBJ', 4);

define('ANYDB_INT', 'integer');
define('ANYDB_FLOAT', 'float');
define('ANYDB_DOUBLE', 'double');
define('ANYDB_STRING', 'string');
define('ANYDB_BOOL', 'boolean');

////////////////////////////////////////////////////////////////////////
/**
* Abstract base class for db access
*
* This class defines the interface for all the implementing layers.
*
* Don't call or instanciate this class.
*
* @link http://www.phpclasses.org/anydb Visit www.phpclasses.org for the latest version
* @author	    Lennart Groetzbach <lennartg@web.de>
* @copyright	Lennart Groetzbach <lennartg@web.de> - distributed under the LGPL
* @version 	   2004/08/26
*
* @package      anydb
* @abstract
* @access       public
*/
////////////////////////////////////////////////////////////////////////

class AbstractDB extends UtilityClass {

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
// public variables
////////////////////////////////////////////////////////////////////////
/**
* db host name
*
* @access   public
* @var     String
*/
var $host = '';
/**
* db name
*
* @access   public
* @var      String
*/
var $database = '';

/**
* user name
*
* @access   public
* @var      String
*/
var $user = '';

/**
* user password
*
* @access   public
* @var      String
*/
var $password = '';

/**
* database resource
*
* @access   public
* @var      Mixed
*/
var $db = null;

/**
* result resource
*
* @access   public
* @var      Mixed
*/
var $result = null;

/**
* the preferred result type
*
* @access   public
* @var      Integer
*/
var $prefResType;

/**
* the last submitted query
*
* @access   public
* @var      String
*/
var $lastQuery = '';

/**
* use a persistent db connection?
*
* @access   public
* @var     Boolean
*/
var $persistent;

/**
* last error
*
* @access   public
* @var      String
*/
var $error = '';

/**
* error array
*
* @access   public
* @var      Array
*/
var $errors = array();

/**
* error array
*
* @access   public
* @var      Array
*/
var $queries = array();


var $pushError = false;


////////////////////////////////////////////////////////////////////////
// constructor
////////////////////////////////////////////////////////////////////////
/**
* Constructor
*
* @abstract
* @access   public
*
* @param    String      $libraryPath        path to the database abstraction layer
* @param    String      $dbType             identifier of the db type
* @param    Integer     $preferredResType   the preferred result type
*/
function AbstractDB($libraryPath, $dbType, $preferredResType = ANYDB_RES_ASSOC) {
}

////////////////////////////////////////////////////////////////////////
// public functions
////////////////////////////////////////////////////////////////////////
/**
* Connects to the db
*
* @abstract
* @access   public
*
* @param    String      $host
* @param    String      $db
* @param    String      $user
* @param    String      $password
* @param    Boolean     $persistent
*
* @return   Boolean     TRUE, if successful
*/
function connect($host, $db, $user, $password, $persistent = false) {
}

////////////////////////////////////////////////////////////////////////
/**
* Closes the db connection
*
* @abstract
* @access   public
*
* @return   Boolean     TRUE, if successful
*/
function disconnect() {
}

////////////////////////////////////////////////////////////////////////
/**
* Submits an sql statement to the db
*
*
* @abstract
* @access   public
*
* @param    String      $query
*
* @return   Boolean     TRUE, if successful
*/
function query($query) {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns the next row in an array
*
* @abstract
* @access   public
*
* @param    Integer     $resultType         should the array have numeric, associative keys, or both
*
* @return   Mixed       1-dimensional array or FALSE
*/
function getNext($resultType = ANYDB_PREDEFINED_VALUE) {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns the resulting table in an 2-dimensional array
*
* @abstract
* @access   public
*
* @param    Integer     $resultType         should the array have numeric, associative keys, or both
*
* @return   Mixed       2-dimensional array
*/
function getAll($resultType = ANYDB_PREDEFINED_VALUE) {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns a single value after an apropriate sql statement
*
* @abstract
* @access   public
*
* @return   Mixed       String or FALSE
*/
function getValue($resultType = ANYDB_STRING) {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns a result column after an apropriate sql statement
*
* @abstract
* @access   public
*
* @param    Integer     $resultType         should the array have numeric, associative keys, or both
*
* @return   Mixed       1-dimensional array or FALSE
*/
function getColumn($resultType = ANYDB_PREDEFINED_VALUE) {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns the resulting table but uses the $key field as an index
*
* @abstract
* @access   public
*
* @param    String     $key        the index key
*
* @return   Mixed       1- or 2-dimensional array or FALSE
*/
function getMapped($key) {
}

////////////////////////////////////////////////////////////////////////
/**
* Executes a statement and returns the result table
*
* @abstract
* @access   public
*
* @param    String      $query              a sql statement
* @param    Integer     $resultType         should the array have numeric, associative keys, or both
*
* @return   Mixed       2-dimensional array or FALSE
*/
function execute($query, $resultType = ANYDB_PREDEFINED_VALUE) {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns the id of the last insert operation
*
* @abstract
* @access   public
*
* @return   Mixed   int or FALSE
*/
function getInsertId() {
}

////////////////////////////////////////////////////////////////////////
/**
* Checks if a dataset exists and returns the id if so
*
* @abstract
* @access   public
*
* @param    String      $table              the table name
* @param    Array       $array              the array to be checked
* @param    Array       $fields             what fields to be checked
* @param    String      $id_field           id field name
*
* @return   Mixed   id on hit or FALSE, null on ERROR
*/
function getDataId($table, $array, $fields = null, $id_field = 'id') {
}
////////////////////////////////////////////////////////////////////////
/**
* Frees the memory from the result set
*
* @abstract
* @access   public
*
* @return   Mixed       TRUE, if successful
*/
function free() {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns how many rows are in the result set
*
* @abstract
* @access   public
*
* @return   Mixed       TRUE, if successful
*/
function numRows() {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns how many rows were affected by the last statement
*
* @abstract
* @access   public
*
* @return   Mixed       Integer or FALSE
*/
function affectedRows() {
}

////////////////////////////////////////////////////////////////////////
/**
* return id string
*
* @abstract
* @access   public
*
* @return   Mixed       String or FALSE
*/
function getIdentifier() {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns the db type identifier
*
* @abstract
* @access   public
*
* @return   String
*/
function getDbType() {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns the current version
*
* @abstract
* @access   public
*
* @return   Integer
*/
function getVersion() {
}
////////////////////////////////////////////////////////////////////////
/**
* Changes the current database
*
* @abstract
* @access   public
*
* @return   Boolean
*/
function setDB() {
}

////////////////////////////////////////////////////////////////////////
/**
* Modifies a string to make it secure to add to the db
*
* @abstract
* @access   public
*
* @param    Mixed       $str        a string or an array
* @param    Array       $keys       if not null it specifies which key names to use
*
* @return   Mixed       String or FALSE
*/
function escapeStr($str, $keys = null) {
}

////////////////////////////////////////////////////////////////////////
/**
* Returns the table names of the current db
*
* @abstract
* @access   public
*
* @return   Mixed       Array or FALSE
*/
function getTables() {
}
////////////////////////////////////////////////////////////////////////
/**
* Returns the names of the available dbs
*
* @abstract
* @access   public
*
* @return   Mixed       Array or FALSE
*/
function getDBs() {
}

////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////
?>