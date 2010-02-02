<?php
//NOTE:
// no native "getAll" because it does not return an associative array

////////////////////////////////////////////////////////////////////////
/**
* Implementation for Metabase
*
* This class implements the db layer for the Metabase db abstraction layer.<br>
* Tested with version 2003/01/03 of Metabase
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

class MetabaseDB extends BasicDB {

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

function MetabaseDB($libraryPath, $dbType, $preferredResType = ANYDB_RES_ASSOC) {
	$par = get_parent_class($this);
	$this->$par($libraryPath, $dbType, $preferredResType);

    $this->_id = 'METABASE';
    require_once $this->_path . "metabase_interface.php";
    require_once $this->_path . "metabase_database.php";
    require_once $this->_path . "metabase_" . $dbType . ".php";
}

////////////////////////////////////////////////////////////////////////

function getVersion() {
    return '1.2 - 11/30/04';
}

////////////////////////////////////////////////////////////////////////

function connect($host, $db, $user, $password, $persistent = false) {
    parent::connect($host, $db, $user, $password, $persistent);

    $args = array();
    $args['Type'] = $this->_dbType;
    $args['User'] = $user;
    $args['Password'] = $password;
    $args['Host'] = $host;
    $args['Persistent'] = ($persistent ? 1 : 0) ;

    $error = MetabaseSetupDatabase($args, $this->db);
    if ($error != null) {
        $this->error = $error;
        return false;
    } else {
        MetabaseSetDatabase($this->db, $db);
        return true;
    }
}

////////////////////////////////////////////////////////////////////////

function disconnect() {
    if ($this->db != null) {
        MetabaseCloseSetup($this->db);
    }
    $this->_afterDisconnect();
    return true;
}

////////////////////////////////////////////////////////////////////////

function query($query) {
   if (parent::query($query)) return true;
    $this->_count = 0;

    $result = MetabaseQuery($this->db, $query);
    if ($result == 0) {
        $this->error = MetabaseError($this->db);
        return false;
    } else {
        $this->result = $result;
        return true;
    }
}

////////////////////////////////////////////////////////////////////////

function getValue($resultType = ANYDB_STRING) {
    if (MetabaseFetchResultField($this->db, $this->result, $res) == 1) {
         if ($resultType != ANYDB_STRING) {
            if (!@settype($res, $resultType)) {
               $this->_addError('Could not convert result', 'getValue()');
               return false;
            }
         }
         return $res;
    } else {
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function getColumn($resultType = ANYDB_PREDEFINED_VALUE) {
    if ($resultType == ANYDB_PREDEFINED_VALUE) {
	$resultType = $this->prefResType;
    }

    if (MetabaseFetchResultColumn($this->db, $this->result, $column) == 1) {
        return $column;
    } else {
        return false;
    }
}
////////////////////////////////////////////////////////////////////////

function getNext($resultType = ANYDB_PREDEFINED_VALUE) {
    if ($resultType == ANYDB_PREDEFINED_VALUE) {
	$resultType = $this->prefResType;
    }
    $n = MetabaseNumberOfColumns($this->db, $this->result);
    if ($this->_count >= $this->numRows()) {
        return null;
    }

    $success = MetabaseFetchResultArray($this->db, $this->result, $row, $this->_count);
    if ($success == 1) {
        $this->_count++;

        switch ($resultType) {
            case ANYDB_RES_ASSOC:
                return $this->_getAssociativeEntries($row);
                break;
            default:
            case ANYDB_RES_NUM:
                return $row;
                break;
            case ANYDB_RES_BOTH:
                return array_merge($row, $this->_getAssociativeEntries($row));
                break;
            default:
	          $this->_addError('Wrong result type!', 'getNext()');
                return false;
        }
    } else {
        return null;
    }
}

////////////////////////////////////////////////////////////////////////

function free() {
//    $success = MetabaseFreeResult($this->db, $this->result);
    if (@$success === true) {
        return true;
    } else {
        $this->error = @$success;
        return false;
    }
}

////////////////////////////////////////////////////////////////////////

function numRows() {
    return MetabaseNumberOfRows($this->db, $this->result);
}

////////////////////////////////////////////////////////////////////////

function affectedRows() {
    $success = MetabaseAffectedRows($this->db, $rows);
    if ($success != null) {
        return $rows;
    }
}

////////////////////////////////////////////////////////////////////////
// private functions
////////////////////////////////////////////////////////////////////////
/**
* Returns only the associative entries of a result
*
* @abstract
* @access   private
*
* @param    Array	$array
*
* @return   Array
*/
function _getAssociativeEntries($array) {
        MetabaseGetColumnNames($this->db, $this->result, $cols);
        $res = array();
        $cols = array_keys($cols);
        $i = 0;
        foreach($array as $value) {
            $res[$cols[$i++]] = $value;
        }
        return $res;
}

////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////

?>