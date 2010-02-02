<?php
////////////////////////////////////////////////////////////////////////
/**
* Implementation for PostgreSQL
*
* This class implements the db interface for PostgreSQL<br>
* Made for php version >= 4.2
* tested with Nusphere UltraSQL 1.0.5b
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

class PostgresDB extends BasicDB {

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
* row count for getNext()
*
* @access   private
* @var     Integer
*/
var $_count = 0;

////////////////////////////////////////////////////////////////////////

function getVersion() {
    return '1.2 - 11/30/04';
}

////////////////////////////////////////////////////////////////////////

function PostgresDB($libraryPath, $dbType = 'pgsql', $preferredResType = ANYDB_RES_ASSOC) {
   if (!extension_loaded('pgsql')) {
      echo 'PGSQL extension not loaded into PHP!';
      die();
   }
	$par = get_parent_class($this);
	$this->$par($libraryPath, $dbType, $preferredResType);

    $this->_id = 'PGSQL';
}

////////////////////////////////////////////////////////////////////////

function connect($host, $db, $user, $password, $persistent = false) {
    parent::connect($host, $db, $user, $password, $persistent);

    $dns = "host=$host dbname=$db user=$user password=$password";
    if ($persistent) {
        $this->db = @pg_pconnect($dns);
    } else {
        $this->db = @pg_connect($dns);
    }
    if ($this->db) {
        return true;
    }else {
        $this->_addError("Error connecting to db!", 'connect()');
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function disconnect() {
    parent::disconnect();
    if ($this->db != null) {
        @pg_close($this->db);
        $this->_afterDisconnect();
        return true;
    } else {
        $this->_addError("Not connected to a db!", 'disconnect()');
        return false;
    }

}

////////////////////////////////////////////////////////////////////////

function query($query) {
    parent::query($query);
	$this->_count = 0;
    if ($this->db != null) {
        // submit query
        $this->result = @pg_query($this->db, $query);
        if ($this->result != false) {
            return true;
        // save error msg
        } else {
            $this->_addError(@pg_last_error($this->db), "query($query)");
            return false;
        }
    } else {
        return false;
    }

}

////////////////////////////////////////////////////////////////////////

function getTables() {
   $this->query('select tablename from pg_tables where tableowner = current_user');
   return $this->getColumn();
}

////////////////////////////////////////////////////////////////////////

function getDBs() {
   $this->query("SELECT datname FROM pg_database WHERE datname !~ '^template';");
   return $this->getColumn();
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
                $res =  @pg_fetch_array($this->result, $this->_count, PGSQL_ASSOC);
                break;
            case ANYDB_RES_NUM:
                $res = @pg_fetch_array($this->result, $this->_count, PGSQL_NUM);
                break;
            case ANYDB_RES_BOTH:
                $res = @pg_fetch_array($this->result, $this->_count, PGSQL_BOTH);
                break;
            case ANYDB_RES_OBJ:
                $res =  @pg_fetch_object($this->result);
                break;
            default:
                $this->_addError("Wrong result type!", 'getNext()');
        }
	$this->_count += 1;
    }
    return $res;
}

////////////////////////////////////////////////////////////////////////

function free() {
    if ($this->result != null) {
        return @pg_free_result($this->result);
    }
}
////////////////////////////////////////////////////////////////////////

function numRows() {
    if ($this->result != null) {
        return @pg_num_rows($this->result);
    } else {
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function affectedRows() {
    if ($this->db != null) {
        return @pg_affected_rows($this->db);
    } else {
        return false;
    }
}
////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////

?>