<?php
////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/AbstractDB.php';
//require_once dirname(__FILE__) . '/../addon/QueryHelper.php';

////////////////////////////////////////////////////////////////////////
/**
* Essential functionality for the db class
*
* This class implements the essential functions for all the implementing layers.<br>
* Don't call or instanciate this class.
*
* @link        http://lensphp.sourceforge.net for the latest version
* @author	   Lennart Groetzbach <lennartg[at]web.de>
* @copyright	Lennart Groetzbach <lennartg[at]web.de> - distributed under the LGPL
*
* @package      anydb
* @abstract
* @access       public
* @version      11/18/04
*/
////////////////////////////////////////////////////////////////////////

class BasicDB extends AbstractDB {

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
// private vars
////////////////////////////////////////////////////////////////////////

/**
* path to the db abstracion layer
*
* @access   private
* @var     String
*/
var $_path;

/**
* db type
*
* @access   private
* @var     String
*/
var $_dbType;

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
function BasicDB($libraryPath, $dbType, $preferredResType = ANYDB_RES_ASSOC) {
   if (!$dbType) {
      echo 'DB type not specified!';
      die();
   }
    $this->prefResType = $preferredResType;
    $this->_dbType = $dbType;
    // remove blanks from path
    $this->_path = trim($libraryPath);
    // add trailing '/' if needed
    if ($this->_path != '') {
        if (substr($this->_path, -1) != "/") {
            $this->_path .= '/';
        }
    }
    $this->_id = 'ABSTRACT BASE CLASS';
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
    $this->free();
    $this->host = $host;
    $this->database = $db;
    $this->user = $user;
    $this->password = $password;
    $this->persistent = $persistent;
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
function setDB($database) {
   return $this->connect($this->host, $database, $this->user, $this->password, $this->persistent);
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
   $this->error = '';
   $this->lastQuery = $query;
   // call sqlites alter?
   if ((strtolower($this->_dbType) == 'sqlite') && (strtolower(substr(ltrim($query),0,5))=='alter')) {
        $queryparts = preg_split("/[\s]+/",$query,4,PREG_SPLIT_NO_EMPTY);
        $tablename = $queryparts[2];
        $alterdefs = $queryparts[3];
        if(strtolower($queryparts[1]) != 'table' || $queryparts[2] == '') {
//          $this->error = 'near "'.$queryparts[0] . '": syntax error';
      } else {
          return $this->_sqliteAlterTable($tablename, $alterdefs);
        }
        return false;
      }

   $this->queries[]= array(
      'timestamp' => time(),
      'query_str' => $query,
      'trace' => $this->_trace()
      );
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
    if ($resultType == ANYDB_PREDEFINED_VALUE) {
	$resultType = $this->prefResType;
    }
    $res = array();
    // append all results in an array
    while ($col = $this->getNext($resultType)) {
        $res[] = $col;
    }
    return $res;
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
	$res = $this->_fixType($this->getNext());
	//print_r($res);
   // was result a single value?
    if (!is_array($res)) {
      // was a result type set?
      if ($resultType != ANYDB_STRING) {
         // set result type
         if (!@settype($res, $resultType)) {
            $this->_addError('Could not convert result', 'getValue()');
            return false;
         }
      }
      return $res;
   }
   $this->_addError('Result was not a single value', 'getValue()');
   return false;
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
    if ($this->query($query)) {
        return $this->getAll($resultType);
    }
    return false;
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
    if ($resultType == ANYDB_PREDEFINED_VALUE) {
	$resultType = $this->prefResType;
    }
    $res = false;
    $array = $this->getAll($resultType);
    if (is_array($array)) {
       foreach ($array as $element) {
           $value = $this->_fixType($element, $resultType);
            if (is_array($value)) {
               $this->_addError('Result was not a single column', 'getColumn()');
               return false;
            }
            if ($value != false) {
               $res[] = $value;
           }
       }
   }
   return $res;
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
   $array = $this->getAll(ANYDB_RES_ASSOC);
   if ($array) {
      $size = sizeof($array[0]);
      // are results there?
      if (($size > 1) && isset($array[0][$key])) {
         $res = array();
         foreach ($array as $entry) {
            // get the key value
            $k = $entry[$key];
            // if result has only two fields, return 1-dim array
            if ($size == 2) {
               unset($entry[$key]);
               $res[$k] = array_pop($entry);
            // else 2-dim
            } else {
               $res[$k] = $entry;
               unset($res[$k][$key]);
            }
         }
         return $res;
      }
   }
   return false;
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
    return true;
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
    return $this->_dbType;
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
    return $this->_id;
}

////////////////////////////////////////////////////////////////////////
function getColumnDescr($table) {
    switch ($this->_dbType) {
        case 'mysql':
            $tmp = $this->execute("describe $table");
            break;
        case 'odbc':
            $tmp = odbc_columns($this->db);
            break;
   }
   return false;
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
    switch ($this->_dbType) {
        case 'dbx':
            $this->query("show tables from $this->database");
            break;
    // mysql
        case 'mysql':       // phplib // metabase // pear // adodb
        case 'mysqll':      // adodb
        case 'mysqlt':      // adodb
            $this->query('show tables');
            break;
    // postgresql
        case 'pgsql':       // phplib // metabase // pear
        case 'postgres':    // ado
        case 'postgres64':  // ado
        case 'postgres7':   // ado
            $this->query('select tablename from pg_tables where tableowner = current_user');
            break;
    // oracle
        case 'oracle':      // phplib // ado
        case 'oci':         // metabase
        case 'oci8':        // ado // pear // phplib
        case 'oci8po':      // ado
        case 'odbc_oracle': // ado
            $this->query('SELECT * FROM cat');
            break;
    // microsoft sql
        case 'mssql':       // metabase // pear // phplib // adodb
        case 'odbc_mssql':  // adodb
        case 'mssqlpo':     // adodb
            $this->query('sp_tables');
            break;
    // interbase
        case 'ibase':       // metabase // pear // adodb
            $this->query('show tables');
            break;
    // mini sql
        case 'msql':        // metabase // pear // phplib
            $this->query('show tables');
            break;
    // informix
        case 'ifx':         // metabase // pear
        case 'informix':    // adodb
            $this->query("SELECT tabname FROM systabnames WHERE dbsname = '" . $this->database . "'");
            break;
    // sybase
        case 'sybase':      // pear // phplib // adodb
            $this->query("select name from sysobjects where type='U'");
            break;
    // frontbase
        case 'frontbase':   // pear
        case 'fbsql':       // adodb
    // or error
        default:
            $this->_addError('Unknown command!', 'getTables()');
            return false;
    }
    return $this->getColumn();
}

////////////////////////////////////////////////////////////////////////

function getDBs() {
    $this->_addError('Function not supported!', 'getDBs()');
   return false;
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
    if (is_array($str)) {
        $res = array();
        foreach ($str as $key => $val) {
            if ($keys && array_key_exists($key, $keys) !== false) {
               $res[$key] = $val;
         } else {
            $res[$key] = $this->escapeStr($val);
         }
        }
        return $res;
    } else {
        switch ($this->_dbType) {
        // mysql
            case 'jdbc':
            case 'mysql':       // phplib // metabase // pear // adodb
            case 'mysqll':      // adodb
            case 'mysqlt':      // adodb
                return mysql_real_escape_string($str);
        // postgresql
            case 'pgsql':       // phplib // metabase // pear
            case 'postgres':    // ado
            case 'postgres64':  // ado
            case 'postgres7':   // ado
                return pg_escape_string($str);
        // sqlite
            case 'sqlite':
                return mysql_escape_string($str);
        // oracle
            case 'oracle':      // phplib // ado
            case 'oci':         // metabase
            case 'oci8':        // ado // pear // phplib
            case 'oci8po':      // ado
            case 'odbc_oracle': // ado
			return str_replace("'","''",str_replace("''","'",stripslashes($str)));
        // microsoft sql
            case 'access':
            case 'mssql':       // metabase // pear // phplib // adodb
            case 'odbc_mssql':  // adodb
            case 'mssqlpo':     // adodb
			$str = str_replace("'","''",str_replace("\'","'",$str));
			$escape = array ( "\n"=>"\\\\012","\r"=>"\\\\015");
			foreach ( $escape as $match => $replace ) {
				$str = str_replace($match, $replace, $str);
			}
			return $str;
        // mini sql
            case 'msql':        // metabase // pear // phplib
                break;
        // interbase
            case 'ibase':       // metabase // pear // adodb
			return str_replace("'","''",str_replace("''","'",stripslashes($str)));
                break;
        // informix
            case 'ifx':         // metabase // pear
            case 'informix':    // adodb
                $str = str_replace ("'", "''", $str );
                $str = str_replace ("\r", "", $str );
                return $str;
        // sybase
            case 'sybase':      // pear // phplib // adodb
                break;
        // frontbase
            case 'frontbase':   // pear
            case 'fbsql':       // adodb
                break;

            case 'sqlite':       // adodb
                return sqlite_escape_string($str);
                break;
        }
        return '';
    }
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
   switch ($this->_id) {
      case 'ADODB':
         return $this->db->Insert_ID();
         break;
      case 'ODBC':
      case 'PEAR':
      case 'METABASE':
         switch ($this->_dbType) {
            case 'sqlite':
               $this->query("SELECT last_insert_rowid()");
               return $this->getValue();
               break;
            case 'mysql':
               $this->query("LAST_INSERT_ID()");
               return $this->getValue();
               break;
            case 'pgsql':
               $this->query("SELECT currval('sequence_name')");
               return $this->getValue();
               break;
         }
         break;
      case 'MYSQL':
         $id = @mysql_insert_id($this->db);
         return ($id === 0 ? false : $id);
         break;
      case 'PGSQL':
         $id = @pg_last_oid($this->db);
         return ($id === 0 ? false : $id);
         break;
      case 'SQLITE':
         $id = @sqlite_last_insert_rowid($this->db);
         return ($id === 0 ? false : $id);
         break;
   }
   $this->_addError('Function not supported!', 'getInsertId()');
   return false;
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
   if (is_array($array)) {
      if ($fields == null) {
         $fields = array_keys($array);
      }
      $data = array();
      foreach ($fields as $key) {
         if (isset($array[$key])) {
            $data[] = "$key='$array[$key]'";
         }
      }
      if (sizeof($data)) {
         $query = "SELECT $id_field FROM $table WHERE " . implode(' AND ', $data);
         if ($this->query($query)) {
            $res = $this->getColumn();
            if ($res) {
               return $res;
            }
          $this->_addError('Empty array list', 'getDataId()');
          return null;

         }
      }
   }
   return false;
}

////////////////////////////////////////////////////////////////////////
// private functions
////////////////////////////////////////////////////////////////////////
/**
* clean up function
*
* @abstract
* @access   private
*/
function _afterDisconnect() {
    // clear all the values
    $this->db = null;
    $this->host = '';
    $this->database = '';
    $this->user = '';
    $this->password = '';
    return true;

}

////////////////////////////////////////////////////////////////////////
/**
* removes an array depth
*
* @abstract
* @access   private
*
* @param    Array	$array
* @param    Integer	$resultType
*
* @return   Mixed	1-dim array or String
*/
function _fixType($res, $resultType = ANYDB_RES_NUM) {
    // too much elements?
    $temp = @array_values($res);
    $count = 1;
    if ($resultType == ANYDB_RES_BOTH) {
        $count += 1;
    }
    if (sizeof($temp) == $count) {
           return $temp[0];
    } else {
        return $res;
    }
}

////////////////////////////////////////////////////////////////////////
/**
* Returns only the (renumbered) numeric entries of an array
*
* @abstract
* @access   private
*
* @param    Array	$array
*
* @return   Array
*/
function _getNumericEntries($array) {
    if ($array != null) {
	$res = array();
        $i = 0;
        foreach($array as $value) {
            $res[$i++] = $value;
        }
    }
    return $res;
}

////////////////////////////////////////////////////////////////////////

function _trace() {
   $tmp = debug_backtrace();
   $res = '';
   foreach($tmp as $entry) {
      if (@$entry['file']) {
         $res .= '[' . basename($entry['file']) . ' : ' . $entry['line'] . '] ';
      }
   }
   return trim($res);
}

////////////////////////////////////////////////////////////////////////

function _addError($err_str, $where) {
   global $debug;
   $entry = array(
      'timestamp' => time(),
      'err_str' => $err_str,
      'where' => $where,
      'trace' => $this->_trace()
      );
   $this->error = $err_str;
   if ($this->pushError) {
      $this->errors[] = $entry;
   } else {
      array_unshift($this->errors, $entry);
   }
}

////////////////////////////////////////////////////////////////////////
/**
* Returns only the associative entries of an array
*
* @abstract
* @access   private
*
* @param    Array	$array
*
* @return   Array
*/
function _getAssociativeEntries($array) {
    if ($array != null) {
        $res = array();
        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                $res[$key] = $value;
            }
        }
        return $res;
    }
}

////////////////////////////////////////////////////////////////////////
/**
* Implementes the alter statement for sqlite
* based in SQLITEDB from jon[at]jenseng.com
*
* @access   private
*
* @param    String $table
* @param    String $alterdefs
*
* @return   Boolean
*/
function _sqliteAlterTable($table, $alterdefs){
   if($alterdefs == '') {
      return false;
   } else {
     if ($this->query("SELECT sql,name,type FROM sqlite_master WHERE tbl_name = '".$table."' ORDER BY type DESC")) {
       $row = $this->getNext(); //table sql
       $tmpname = 't'.time();
       $origsql = trim(preg_replace("/[\s]+/"," ",str_replace(",",", ",preg_replace("/[\(]/","( ",$row['sql'],1))));
       $createtemptableSQL = 'CREATE TEMPORARY '.substr(trim(preg_replace("'".$table."'",$tmpname,$origsql,1)),6);
       $createindexsql = array();
       $i = 0;
       $defs = preg_split("/[,]+/",$alterdefs,-1,PREG_SPLIT_NO_EMPTY);
       $prevword = $table;
       $oldcols = preg_split("/[,]+/",substr(trim($createtemptableSQL),strpos(trim($createtemptableSQL),'(')+1),-1,PREG_SPLIT_NO_EMPTY);
       $newcols = array();
       for($i=0;$i<sizeof($oldcols);$i++){
         $colparts = preg_split("/[\s]+/",$oldcols[$i],-1,PREG_SPLIT_NO_EMPTY);
         $oldcols[$i] = $colparts[0];
         $newcols[$colparts[0]] = $colparts[0];
       }
       $newcolumns = '';
       $oldcolumns = '';
       reset($newcols);
       while(list($key,$val) = each($newcols)){
         $newcolumns .= ($newcolumns?', ':'').$val;
         $oldcolumns .= ($oldcolumns?', ':'').$key;
       }
       $copytotempsql = 'INSERT INTO '.$tmpname.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$table;
       $dropoldsql = 'DROP TABLE '.$table;
       $createtesttableSQL = $createtemptableSQL;
       foreach($defs as $def){
         $defparts = preg_split("/[\s]+/",$def,-1,PREG_SPLIT_NO_EMPTY);
         $action = strtolower($defparts[0]);
         switch($action){
         case 'add':
           if(sizeof($defparts) <= 2){
               $this->_addError('near "'.$defparts[0].($defparts[1]?' '.$defparts[1]:'').'": syntax error', 'query()');
             return false;
           }
           $createtesttableSQL = substr($createtesttableSQL,0,strlen($createtesttableSQL)-1).',';
           for($i=1;$i<sizeof($defparts);$i++)
             $createtesttableSQL.=' '.$defparts[$i];
           $createtesttableSQL.=')';
           break;
         case 'change':
           if(sizeof($defparts) <= 3){
               $this->_addError('near "'.$defparts[0].($defparts[1]?' '.$defparts[1]:'').($defparts[2]?' '.$defparts[2]:'').'": syntax error', 'query()');
             return false;
           }
           if($severpos = strpos($createtesttableSQL,' '.$defparts[1].' ')){
             if($newcols[$defparts[1]] != $defparts[1]){
               trigger_error('unknown column "'.$defparts[1].'" in "'.$table.'"',E_USER_WARNING);
               return false;
             }
             $newcols[$defparts[1]] = $defparts[2];
             $nextcommapos = strpos($createtesttableSQL,',',$severpos);
             $insertval = '';
             for($i=2;$i<sizeof($defparts);$i++)
               $insertval.=' '.$defparts[$i];
             if($nextcommapos)
               $createtesttableSQL = substr($createtesttableSQL,0,$severpos).$insertval.substr($createtesttableSQL,$nextcommapos);
             else
               $createtesttableSQL = substr($createtesttableSQL,0,$severpos-(strpos($createtesttableSQL,',')?0:1)).$insertval.')';
           }
           else{
               $this->_addError('unknown column "'.$defparts[1].'" in "'.$table.'"', 'query()');
             return false;
           }
           break;
         case 'drop':
           if(sizeof($defparts) < 2){
               $this->_addError('near "'.$defparts[0].($defparts[1]?' '.$defparts[1]:'').'": syntax error', 'query()');
               return false;
           }
           if($severpos = strpos($createtesttableSQL,' '.$defparts[1].' ')){
             $nextcommapos = strpos($createtesttableSQL,',',$severpos);
             if($nextcommapos)
               $createtesttableSQL = substr($createtesttableSQL,0,$severpos).substr($createtesttableSQL,$nextcommapos + 1);
             else
               $createtesttableSQL = substr($createtesttableSQL,0,$severpos-(strpos($createtesttableSQL,',')?0:1) - 1).')';
             unset($newcols[$defparts[1]]);
           }
           else{
               $this->_addError('unknown column "'.$defparts[1].'" in "'.$table.'"', 'query()');
               return false;
           }
           break;
         default:
               $this->_addError('near "'.$prevword.'": syntax error', 'query()');
               return false;
         }
         $prevword = $defparts[sizeof($defparts)-1];
       }

       //this block of code generates a test table simply to verify that the columns specifed are valid in an sql statement
       //this ensures that no reserved words are used as columns, for example
       if (!$this->query($createtesttableSQL)) {
         return false;
       }
       $droptempsql = 'DROP TABLE '.$tmpname;
       $this->query($droptempsql);
       //end block

       $createnewtableSQL = 'CREATE '.substr(trim(preg_replace("'".$tmpname."'",$table,$createtesttableSQL,1)),17);
       $newcolumns = '';
       $oldcolumns = '';
       reset($newcols);
       while(list($key,$val) = each($newcols)){
         $newcolumns .= ($newcolumns?', ':'').$val;
         $oldcolumns .= ($oldcolumns?', ':'').$key;
       }
       $copytonewsql = 'INSERT INTO '.$table.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$tmpname;

// should be atomic
       $this->query('BEGIN');
       $res = $this->query($createtemptableSQL); //create temp table
       if ($res) $res = $this->query($copytotempsql); //copy to table
       if ($res) $res = $this->query($dropoldsql); //drop old table

       if ($res) $res = $this->query($createnewtableSQL); //recreate original table
       if ($res) $res = $this->query($copytonewsql); //copy back to original table
       if ($res) $res = $this->query($droptempsql); //drop temp table
       if ($res) {
          $this->query('COMMIT');
       } else {
          $this->query('ROLLBACK');
          return false;
      }
     } else {
       return false;
     }
   }
   return true;
}

////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////
?>