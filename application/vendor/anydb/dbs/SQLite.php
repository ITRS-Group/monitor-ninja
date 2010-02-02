<?php
////////////////////////////////////////////////////////////////////////
/**
* Implementation for SQLite
*
* This class implements the db interface for SQLite<br>
* Tested with sqlite version 2.8.3
*
* @link        http://lensphp.sourceforge.net for the latest version
* @author	   Lennart Groetzbach <lennartg[at]web.de>
* @copyright	Lennart Groetzbach <lennartg[at]web.de> - distributed under the LGPL
*
* @package      anydb
* @access       public
* @version      1.2 - 11/30/04
*/
////////////////////////////////////////////////////////////////////////

class SQLiteDB extends BasicDB {

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

function getVersion() {
    return '1.2 - 11/30/04';
}

////////////////////////////////////////////////////////////////////////

function SQLiteDB($libraryPath, $dbType = 'sqlite', $preferredResType = ANYDB_RES_ASSOC) {
   if (!extension_loaded('sqlite')) {
      echo 'SQLITE extension not loaded into PHP!';
      die();
   }
	$par = get_parent_class($this);
	$this->$par($libraryPath, $dbType, $preferredResType);

    $this->_id = 'SQLITE';
}



////////////////////////////////////////////////////////////////////////

function connect($host, $db, $user, $password, $persistent = false) {
/*   if (!file_exists($db)) {
      return false;
   }*/
   parent::connect($host, $db, $user, $password, $persistent);
   if ($persistent) {
     $this->db = sqlite_open($db);
   } else {
     $this->db = sqlite_popen($db);
   }
   if ($this->db) {
     return true;
   } else {
     $this->_addError('Error connecting to db!', 'connect()');
     return false;
   }
}

////////////////////////////////////////////////////////////////////////

function disconnect() {
    parent::disconnect();
    if ($this->db != null) {
        @sqlite_close($this->db);
        $this->_afterDisconnect();
        return true;
    } else {
        $this->_addError('Not connected to a db!', 'disconnect()');
        return false;
    }

}

////////////////////////////////////////////////////////////////////////

function query($query) {
   if (parent::query($query)) return true;

    if ($this->db != null) {
        // submit query
        $this->result = @sqlite_unbuffered_query($this->db, $query);
        if ($this->result != false) {
            return true;
        // save error msg
        } else {
            $this->_addError(@sqlite_error_string(sqlite_last_error($this->db)), "query($query)");
            $this->result = null;
            return false;
        }
    } else {
        return false;
    }
}


////////////////////////////////////////////////////////////////////////

function getNext($resultType = ANYDB_PREDEFINED_VALUE) {
    if ($resultType == ANYDB_PREDEFINED_VALUE) {
	$resultType = $this->prefResType;
    }
    $res = false;
    // get next result set
    if ($this->result != null) {
        switch ($resultType) {
            case ANYDB_RES_ASSOC:
                $res =  @sqlite_fetch_array($this->result, SQLITE_ASSOC);
                break;
            case ANYDB_RES_NUM:
                $res =  @sqlite_fetch_array($this->result, SQLITE_NUM);
                break;
            case ANYDB_RES_BOTH:
                $res =  @sqlite_fetch_array($this->result, SQLITE_BOTH);
                break;
            case ANYDB_RES_OBJ:
               if (function_exists('sqlite_fetch_object')) {
                  $res =  @sqlite_fetch_object($this->result);
               } else {
                  $this->_addError("Need PHP5 to fetch an object!", 'getNext(ANYDB_RES_OBJ)');
               }
               break;
            default:
                $this->_addError("Wrong result type!", 'getNext()');
        }
    }
    return $res;
}

////////////////////////////////////////////////////////////////////////

function getAll($resultType = ANYDB_PREDEFINED_VALUE) {
    if ($resultType == ANYDB_PREDEFINED_VALUE) {
	$resultType = $this->prefResType;
    }
   $res = false;
   if ($this->result != null) {
      switch ($resultType) {
         case ANYDB_RES_ASSOC:
            $res = @sqlite_fetch_all($this->result, SQLITE_ASSOC);
		break;
         case ANYDB_RES_NUM:
            $res = @sqlite_fetch_all($this->result, SQLITE_NUM);
		break;
         case ANYDB_RES_BOTH:
            $res = @sqlite_fetch_all($this->result, SQLITE_BOTH);
		break;
         default:
            $this->_addError('Result type not supported!', 'getAll()');
      }
   }
   return $res;
}

////////////////////////////////////////////////////////////////////////

function getValue($resultType = ANYDB_STRING) {
   $res = @sqlite_fetch_string($this->result);
   if (!$res) {
      $this->_addError('No result', 'getValue()');
      return false;
   }
   if ($resultType != ANYDB_STRING) {
      if (!@settype($res, $resultType)) {
         $this->_addError('Could not convert result', 'getValue()');
         return false;
      }
   }
   return $res;
}

////////////////////////////////////////////////////////////////////////

function numRows() {
   return ($this->result != null) ? @sqlite_num_rows($this->result) : false;
}

////////////////////////////////////////////////////////////////////////

function affectedRows() {
   return ($this->result != null) ? @sqlite_changes($this->result) : false;
}

////////////////////////////////////////////////////////////////////////

function getTables() {
   $this->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
   return $this->getColumn();
}

////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////

?>