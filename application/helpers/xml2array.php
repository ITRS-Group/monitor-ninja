<?php
/* Copyright (C) 2004 Indymedia Video Distribution Network.
 *
 * This file is part of Nimiq.
 *
 * Nimiq is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Nimiq is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Nimiq; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * In addition, as a special exception, the Nimiq authors give permission to
 * link the code of this program with the PHP PEAR::DB library (or with
 * modified versions of PEAR::DB that use the same license as PEAR::DB),
 * and distribute linked combinations including the two. You must obey the
 * GNU General Public License in all respects for all of the code used other
 * than PEAR::DB. If you modify this file, you may extend this exception to
 * your version of the file, but you are not obligated to do so. If you do
 * not wish to do so, delete this exception statement from your version.
 *
 * $Id: XML2Array.class.php,v 1.2 2006/03/08 20:54:35 occam Exp $
 *
 */

// {{{ class XML2Array
/**
 * XML2Array class.
 *
 * @version		$Revision: 1.2 $
 * @author		lexi <lexi@bubbl.net>
 * @package		XML
 * @todo		dirty class, rewrite it
 */
class xml2array_Core {
	var $_result = Array();
	var $_depth = 0;
	var $_parent = Array();
	var $_cdp = Array();
	var $_parser = NULL;

	// {{{ constructor
	/**
	 * Class constructor.
	 *
	 * @param string  $file the file path to read
	 */
	function XML2Array($xmldata, $dontvalidate = false)
	{
		//_log("XML2Array::XML2Array(): $file",
		//		 LOG_LEVEL_DEBUG);

		if (!$dontvalidate && function_exists("domxml_open_mem") && function_exists("domxml_doc_validate")) {
			// do an errorcheck and dtd validation

			$error = array();
			$dom = @domxml_open_mem($xmldata, DOMXML_LOAD_VALIDATING, &$error);

			if(!is_object($dom)){
				$this->_log("XML2Array::XML2Array(): error while loading xmldata",
					 LOG_LEVEL_ERROR);
				$this = NULL;
				return NULL;
			}

			// validate against DTD
			if(!@domxml_doc_validate($dom, &$error)){
				$this->_log("XML2Array::XML2Array(): xmldata is invalid:",
					 LOG_LEVEL_ERROR);

				foreach ($error as $e) {
					$this->_log("XML2Array::XML2Array(): line " .
						$e['line'] . ": <" . $e['nodename'] . ">: " .
						$e['errormessage'] . "",
						LOG_LEVEL_ERROR);
				}

				$dom->free();
				$this = NULL;
				return NULL;
			}

			$dom->free();
		} else {
		   // $this->_log("XML2Array::XML2Array(): no domxml support/not validating",
		   //	  LOG_LEVEL_DEBUG);
		}

		// initialize parser
		$this->_parser = xml_parser_create();
		xml_set_object($this->_parser, &$this);
		xml_set_element_handler($this->_parser, "_xmlStartElement", "_xmlEndElement");
		xml_set_character_data_handler($this->_parser, "_xmlCDATA");
		$this->_parent[0] =& $this->_result;
		$this->_cdp[0] = NULL;

		// parse
		if (! $this->_parse($xmldata)) {
			$this->_log("XML2Array::XML2Array(): parser error",
				LOG_LEVEL_ERROR);
			xml_parser_free($this->_parser);
			$this = NULL;
			return NULL;
		}

		xml_parser_free($this->_parser);
	}

	public function _log($msg = false)
	{
		$msg = addslashes(trim($msg));
		if (empty($msg)) {
			return false;
		}
		Kohana::log('error', $msg);
	}

	// }}}
	// {{{ get()
	/**
	 * Returns the parsed array.
	 *
	 * @return array
	 */
	function get()
	{
		return $this->_result;
	}

	// }}}

	// private:
	// {{{ _parse()
	/**
	 * @access private
	 */
	function _parse($data) {
		if (! xml_parse($this->_parser, $data, true)) {
			$this->_log(sprintf("XML2Array::_parse(): XML error: %s at line %d\n",
						xml_error_string(xml_get_error_code($this->_parser)),
						xml_get_current_line_number($this->_parser)),
					LOG_LEVEL_ERROR);
			$this->_log($data, LOG_LEVEL_DEBUG);
			return false;
		}

		return true;
	  }

	// }}
	/**
	 * @access private
	 */
	function _xmlStartElement($parser, $name, $attrs)
	{
		$layer = sizeof($this->_parent[$this->_depth][$name]);

		$my_attrs = Array();
		foreach ($attrs as $attr => $val) {
			$my_attrs[$attr] = $val;
			$my_attrs[strtolower($attr)] =& $my_attrs[$attr];
		}

		$this->_parent[$this->_depth][$name][$layer]['attrs'] = $my_attrs;
		$this->_parent[$this->_depth][$name][$layer]['childs'] = Array();
		$this->_parent[$this->_depth][$name][$layer]['cdata'] = '';

		$this->_parent[$this->_depth][strtolower($name)][$layer]['attrs'] =&
		$this->_parent[$this->_depth][$name][$layer]['attrs'];
		$this->_parent[$this->_depth][strtolower($name)][$layer]['childs'] =&
		$this->_parent[$this->_depth][$name][$layer]['childs'];
		$this->_parent[$this->_depth][strtolower($name)][$layer]['cdata'] =&
		$this->_parent[$this->_depth][$name][$layer]['cdata'];

		$this->_depth++;
		$this->_parent[$this->_depth] =& $this->_parent[$this->_depth-1][$name][$layer]['childs'];
		$this->_cdp[$this->_depth] =& $this->_parent[$this->_depth-1][$name][$layer]['cdata'];
	}


	/**
	 * @access private
	 */
	function _xmlEndElement($parser, $name)
	{
		$this->_depth--;
	}


	/**
	 * @access private
	 */
	function _xmlCDATA($parser, $cdata)
	{
		if ($cdata == "\n" || $cdata == "\r\n" || $cdata == "\t" || $cdata == "") {
			return;
		}

		$this->_cdp[$this->_depth] .= ((!empty($this->_cdp[$this->_depth])) ? "\n" : '') . trim($cdata);
	}
}

// }}}
?>