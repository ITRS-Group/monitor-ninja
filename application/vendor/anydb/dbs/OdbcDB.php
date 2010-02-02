<?php
////////////////////////////////////////////////////////////////////////
/**
* Implementation for ODBC
*
* This class implements the db interface for ODBC<br>
* Made for php version >= 4.2
* tested with MS-Access
*
* @link        http://lensphp.sourceforge.net for the latest version
* @author	   Lennart Groetzbach <lennartg[at]web.de>
* @copyright	Lennart Groetzbach <lennartg[at]web.de> - distributed under the LGPL
*
* @package      anydb
* @access       public
* @version      1.2 - 11/30/04
*
*/////////////////////////////////////////////////////////////////////////

class OdbcDB extends BasicDB {

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

/**
* connection dns
*
* @access   private
* @var     Integer
*/
var $dsn = '';

////////////////////////////////////////////////////////////////////////

function OdbcDB($libraryPath, $dbType, $preferredResType = ANYDB_RES_ASSOC) {
	$par = get_parent_class($this);
	$this->$par($libraryPath, $dbType, $preferredResType);
   $this->_id = 'ODBC';
}

////////////////////////////////////////////////////////////////////////

function getVersion() {
    return '1.2 - 11/30/04';
}

////////////////////////////////////////////////////////////////////////


function setDB($dns) {
   $this->connect($dns, '', $this->user, $this->password);
}
////////////////////////////////////////////////////////////////////////

function connect($dsn, $db = '', $user, $password, $persistent = false) {
   parent::connect($dsn, $db, $user, $password, $persistent);
   $this->dsn = $dsn;
   $this->host = '';
   $this->db = '';
   if ($persistent) {
     $this->db = odbc_pconnect($dsn, $user, $password);
   } else {
     $this->db = odbc_connect($dsn, $user, $password);
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
        odbc_close($this->db);
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

   if ($this->db) {
      $this->result = @odbc_exec($this->db, $query);
      if (!$this->result) {
         $this->_addError(odbc_errormsg($this->db), "query($query)");
      } else {
         return true;
      }
   }
   return false;
}

////////////////////////////////////////////////////////////////////////

function getTables() {
   $this->result = odbc_tables($this->db);
   if ($this->result) {
      foreach ($this->getAll() as $entry) {
         if ($entry['TABLE_TYPE'] != 'SYSTEM TABLE') {
            $res[] = $entry['TABLE_NAME'];
         }
      }
   } else {
         $this->_addError(odbc_errormsg($this->db), "getTables");
   }
   return @$res;
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
                return @odbc_fetch_array($this->result);
                break;
            case ANYDB_RES_NUM:
                return @array_values(odbc_fetch_array($this->result));
                break;
            case ANYDB_RES_BOTH:
                $res = @odbc_fetch_array($this->result);
                return @array_merge($res, array_values($res));
                break;
            case ANYDB_RES_OBJ:
                return @odbc_fetch_object($this->result);
                break;
            default:
	          $this->_addError('Wrong result type!', 'getNext()');
                return false;
        }
    } else {
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function free() {
    if ($this->result != null) {
        return @odbc_free_result($this->result);
    }
}

////////////////////////////////////////////////////////////////////////

function numRows() {
   if ($this->result != null) {
      $res = odbc_num_rows($this->result);
      return $res;
   } else {
      return false;
   }
}

////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////
