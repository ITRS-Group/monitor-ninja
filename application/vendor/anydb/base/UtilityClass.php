<?php
////////////////////////////////////////////////////////////////////////
/**
* Top level utility class
*
* This class allows to create utility classes in php.
*
* @link        http://lensphp.sourceforge.net for the latest version
* @author	   Lennart Groetzbach <lennartg[at]web.de>
* @copyright	Lennart Groetzbach <lennartg[at]web.de> - distributed under the LGPL
*
* @package      anydb
*
* @access       public
* @abstract
* @static
* @version      09/02/03
*/
////////////////////////////////////////////////////////////////////////

class UtilityClass {

////////////////////////////////////////////////////////////////////////

function UtilityClass() {
	trigger_error("Can't use a new() on a utility class, use Class::method() instead ", E_USER_ERROR);
	return null;
}

////////////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////////////
?>