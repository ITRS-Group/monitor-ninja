<?php
////////////////////////////////////////////////////////////////////////
/**
* Implementation for ADODB
*
* This class implements the db interface for the ADODB abstraction layer.<br>
* Tested with version 3.5, 4.2.1, 4.5.1 of ADODB
*
*
* @link        http://lensphp.sourceforge.net for the latest version
* @author	   Lennart Groetzbach <lennartg[at]web.de>
* @copyright	Lennart Groetzbach <lennartg[at]web.de> - distributed under the LGPL
*
* @package      anydb
* @access       public
* @version      1.2 - 11/30/04
*
*/
////////////////////////////////////////////////////////////////////////

class ADOdbDB extends BasicDB {

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

/**
* previous restult type
*
* @access   private
* @var     String
*/
var $_last = ANYDB_PREDEFINED_VALUE;

////////////////////////////////////////////////////////////////////////

function getVersion() {
    $res = '1.2 - 11/30/04';
    return $res;
}

////////////////////////////////////////////////////////////////////////

function ADOdbDB($libraryPath, $dbType, $preferredResType = ANYDB_RES_ASSOC) {
   $par = get_parent_class($this);
   $this->$par($libraryPath, $dbType, $preferredResType);
   $this->_id = 'ADODB';
   require_once $this->_path . 'adodb.inc.php';
}

////////////////////////////////////////////////////////////////////////

function connect($host, $db, $user, $password, $persistent = false) {
    parent::connect($host, $db, $user, $password, $persistent);

    $this->db = &ADONewConnection($this->_dbType);
    if ($persistent) {
        $res = $this->db->PConnect($host, $user, $password, $db);
    } else {
        $res = $this->db->Connect($host, $user, $password, $db);
    }
    return $res;
}

////////////////////////////////////////////////////////////////////////

function disconnect() {
    if ($this->db != null) {
        $this->db->Close();
        $this->_afterDisconnect();
        return true;
    } else {
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function query($query) {
   if (parent::query($query)) return true;
	$this->_count = 0;
    if ($this->db != null) {
        $res = & $this->db->Execute($query);
        if (!$res) {
            $this->_addError($this->db->ErrorMsg(), "query($query)");
            $this->result = null;
            return false;
        }
        else {
            $this->result = $res;
            return true;
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
	$count = $this->_count;
	// did the result type change?
	if ($this->_last != $resultType) {
		$this->_last = $resultType;
		switch ($resultType) {
            case ANYDB_RES_BOTH:
				$this->db->SetFetchMode(ADODB_FETCH_BOTH);
                break;
            case ANYDB_RES_ASSOC:
				$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
                break;
            case ANYDB_RES_NUM:
				$this->db->SetFetchMode(ADODB_FETCH_NUM);
                break;
            case ANYDB_RES_OBJ:
                break;
            default:
                $this->_addError('Wrong result type!', 'getNext()');
                return false;
                break;
		}
		$this->query($this->lastQuery);
		@$this->result->Move($count);
	}

   if ($resultType == ANYDB_RES_OBJ) {
	$res = @$this->result->FetchObj();
   } else {
	   $res = @$this->result->FetchRow();
   }
	$this->_count = $count + 1;
    return $res;
}

////////////////////////////////////////////////////////////////////////

function getAll($resultType = ANYDB_PREDEFINED_VALUE) {
    if ($resultType == ANYDB_PREDEFINED_VALUE) {
	$resultType = $this->prefResType;
    }
    $res = null;
    if ($this->db != null) {
        switch ($resultType) {
            case ANYDB_RES_BOTH:
				$this->db->SetFetchMode(ADODB_FETCH_BOTH);
                $res = $this->db->getAll($this->lastQuery);
                break;
            case ANYDB_RES_ASSOC:
				$this->db->SetFetchMode(ADODB_FETCH_ASSOC);

                $res = $this->db->getAll($this->lastQuery);
                break;
            case ANYDB_RES_NUM:
				$this->db->SetFetchMode(ADODB_FETCH_NUM);
                $res = $this->db->getAll($this->lastQuery);
                break;
        }
        // was the result an error?
        if (is_a($res, "DB_ERROR")) {
            $this->_addError($res->message, 'getAll()');
            return false;
        } else {
            // return it
            return $res;
        }
    } else {
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function getValue($resultType = ANYDB_STRING) {
    $res = null;
    if ($this->db != null) {
        $res = $this->db->getOne($this->lastQuery);
        if (is_a($res, "DB_ERROR")) {
            $this->_addError($res->message, 'getValue()');
            return false;
        } else {
            // return it
            if ($resultType != ANYDB_STRING) {
               if (!@settype($res, $resultType)) {
                  $this->_addError('Could not convert result', 'getValue()');
                  return false;
               }
            }
            return $res;
        }
    } else {
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function getColumn($resultType = ANYDB_PREDEFINED_VALUE) {
    if ($resultType == ANYDB_PREDEFINED_VALUE) {
	$resultType = $this->prefResType;
    }
    $res = null;
    if ($this->db != null) {
        switch ($resultType) {
            case ANYDB_RES_BOTH:
				$this->db->SetFetchMode(ADODB_FETCH_BOTH);
                $res = $this->db->getCol($this->lastQuery);
                break;
            case ANYDB_RES_ASSOC:
				$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
                $res = $this->db->getCol($this->lastQuery);
                break;
            case ANYDB_RES_NUM:
				$this->db->SetFetchMode(ADODB_FETCH_NUM);
                $res = $this->db->getCol($this->lastQuery);
                break;
        }
        if (is_a($res, "DB_ERROR")) {
            $this->_addError($res->message, 'getColumn()');
            return false;
        } else {
            // return it
            return $res;
        }
    } else {
        return false;
    }
}
////////////////////////////////////////////////////////////////////////

function getInsertId() {
    return $this->db->Insert_ID();
}
////////////////////////////////////////////////////////////////////////

function free() {
    if ($this->result != null) {
        $res = $this->result->Close();
        if ($res == true) {
            $this->result = null;
            return true;
        } else {
            $this->_addError($res->message, 'free()');
            return false;
        }
    }
}

////////////////////////////////////////////////////////////////////////

function numRows() {
   return ($this->result != null) ? $this->result->RecordCount() : false;
}

////////////////////////////////////////////////////////////////////////

function affectedRows() {
   return ($this->result != null) ? $this->result->Affected_Rows() : false;
}

////////////////////////////////////////////////////////////////////////

function getTables() {
   return $this->db->MetaTables();
}
////////////////////////////////////////////////////////////////////////

function getDBs() {
   return $this->db->MetaDatabases();
}
////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////
?>