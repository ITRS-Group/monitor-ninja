<?php
////////////////////////////////////////////////////////////////////////
/**
* Implementation for mysql
* Made for php version >= 4.x
* Tested with mysql 4.0.17
*
* This class implements the db interface for mysql
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

class MysqlDB extends BasicDB {

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

function MysqlDB($libraryPath, $dbType = 'mysql', $preferredResType = ANYDB_RES_ASSOC) {
	$par = get_parent_class($this);
	$this->$par($libraryPath, $dbType, $preferredResType);

    $this->_id = 'MYSQL';
}

////////////////////////////////////////////////////////////////////////

function getVersion() {
    return '1.2 - 11/30/04';
}

////////////////////////////////////////////////////////////////////////

function connect($host, $db, $user, $password, $persistent = false) {
    // call parent
    parent::connect($host, $db, $user, $password, $persistent);
    // try to connect
    if ($persistent) {
        $this->db = mysql_pconnect($host, $user, $password);
    } else {
        $this->db = mysql_connect($host, $user, $password);
    }
    if ($this->db) {
	if (mysql_select_db($db, $this->db)) {
		    return true;
	}
	$this->_addError(@mysql_error($this->db), 'connect()');
    }
    return false;
}

////////////////////////////////////////////////////////////////////////

function setDB($database) {
      parent::setDB($database);
	return mysql_select_db($database, $this->db);
}

////////////////////////////////////////////////////////////////////////

function disconnect() {
    if ($this->db != null) {
        @mysql_close($this->db);
        $this->_afterDisconnect();
        return true;
    } else {
	$this->_addError('Not connected to a db!', 'disconnect()');
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function query($query) {
    parent::query($query);

    if ($this->db != null) {
        // submit query
        $this->result = @mysql_query($query, $this->db);
        if ($this->result != false) {
            return true;
        // save error msg
        } else {
	$this->_addError(@mysql_error($this->db), "query($query)");
        }
    }
    return false;

}

////////////////////////////////////////////////////////////////////////

function getTables() {
   $this->query('show tables');
   return $this->getColumn();
}

////////////////////////////////////////////////////////////////////////

function getDBs() {
   $this->query('show databases');
   return $this->getColumn();
}

////////////////////////////////////////////////////////////////////////

function getNext($resultType = ANYDB_PREDEFINED_VALUE) {
   if ($resultType == ANYDB_PREDEFINED_VALUE) {
      $resultType = $this->prefResType;
   }
   // get next result set
   if ($this->result != null) {
      switch ($resultType) {
         case ANYDB_RES_ASSOC:
            return @mysql_fetch_array($this->result, MYSQL_ASSOC);
            break;
         case ANYDB_RES_NUM:
            return @mysql_fetch_array($this->result, MYSQL_NUM);
            break;
         case ANYDB_RES_BOTH:
            return @mysql_fetch_array($this->result, MYSQL_BOTH);
            break;
         case ANYDB_RES_OBJ:
            return @mysql_fetch_object($this->result);
            break;
         default:
            $this->_addError('Wrong result type!', 'getNext()');
      }
   }
   return false;
}

////////////////////////////////////////////////////////////////////////

function free() {
   return ($this->result !== null) ? @mysql_free_result($this->result) : false;
}
////////////////////////////////////////////////////////////////////////

function numRows() {
   return ($this->result != null) ? @mysql_num_rows($this->result) : false;
}

////////////////////////////////////////////////////////////////////////

function affectedRows() {
   return ($this->result != null) ? @mysql_affected_rows($this->result) : false;
}
////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////
?>