<?php

require_once(HTML2PS_DIR.'encoding.inc.php');
require_once(HTML2PS_DIR.'encoding.entities.inc.php');
require_once(HTML2PS_DIR.'encoding.glyphs.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-1.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-2.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-3.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-4.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-5.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-6.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-7.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-8.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-9.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-10.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-11.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-13.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-14.inc.php');
require_once(HTML2PS_DIR.'encoding.iso-8859-15.inc.php');
require_once(HTML2PS_DIR.'encoding.koi8-r.inc.php');
require_once(HTML2PS_DIR.'encoding.cp866.inc.php');
require_once(HTML2PS_DIR.'encoding.windows-1250.inc.php');
require_once(HTML2PS_DIR.'encoding.windows-1251.inc.php');
require_once(HTML2PS_DIR.'encoding.windows-1252.inc.php');
require_once(HTML2PS_DIR.'encoding.dingbats.inc.php');
require_once(HTML2PS_DIR.'encoding.symbol.inc.php');

// TODO: this works for PS encoding names only
class ManagerEncoding {
  var $_encodings = array();

  /**
   * Number of the current custom encoding vector
   */
  var $_custom_vector_index = 0;

  var $_utf8_mapping;

  function ManagerEncoding() {
    $this->new_custom_encoding_vector();
  }

  /**
   * Add  new  custom symbol  not  present  in  the existing  encoding
   * vectors.
   *
   * Note:  encoding vector  this character  was placed  to  should be
   * extracted via  get_current_custom_encoding_name immediately after
   * add_custom_char call.
   *
   * @param  char[2]  $char UCS-2  character  (represented as  2-octet
   * string)
   *
   * @return char index of this character in custom encoding vector
   */
  function add_custom_char($char) {
    // Check if current  encoding vector is full; if  it is, we should
    // add a new one.
    if ($this->is_custom_encoding_full()) {
      $this->new_custom_encoding_vector();
    };

    // Get name of  the custom encoding where new  character should be
    // placed
    $vector_name = $this->get_current_custom_encoding_name();

    // Get (zero-based) index of this character in the encoding vector
    $index = count($this->_encodings[$vector_name]);

    // Add new character to the custom encoding vector
    $this->_encodings[$vector_name][chr($index)] = $char;

    // Add new character to the UTF8 mapping table
    $this->_utf8_mapping[code_to_utf8($char)][$vector_name] = chr($index);

    return chr($index);
  }

  function generate_mapping($mapping_file) {
    global $g_utf8_converters;

    $this->_utf8_mapping = array();
    foreach (array_keys($g_utf8_converters) as $encoding) {
      $flipped = array_flip($g_utf8_converters[$encoding][0]);
      foreach ($flipped as $utf => $code) {
        $this->_utf8_mapping[code_to_utf8($utf)][$encoding] = $code;
      };
    };

    $file = fopen($mapping_file,'w');
    fwrite($file, serialize($this->_utf8_mapping));
    fclose($file);
  }

  function &get() {
    global $g_manager_encodings;
    return $g_manager_encodings;
  }

  function get_canonized_encoding_name($encoding) {
    global $g_encoding_aliases;

    if (isset($g_encoding_aliases[$encoding])) {
      return $g_encoding_aliases[$encoding];
    };

    return $encoding;
  }

  function get_current_custom_encoding_name() {
    return $this->get_custom_encoding_name($this->get_custom_vector_index());
  }

  function get_custom_encoding_name($index) {
    return sprintf('custom%d', 
                   $index);
  }

  function get_custom_vector_index() {
    return $this->_custom_vector_index;
  }

  function get_encoding_glyphs($encoding) {
    $vector = $this->get_encoding_vector($encoding);
    if (is_null($vector)) { 
      error_log(sprintf("Cannot get encoding vector for encoding '%s'", $encoding));
      return null; 
    };
    return $this->vector_to_glyphs($vector);
  }

  /**
   * Get  an encoding  vector  (array containing  256 elements;  every
   * element is an ucs-2 encoded character)
   *
   * @param $encoding Encoding name
   *
   * @return Array encoding vector; null if this encoding is not known to the script
   */
  function get_encoding_vector($encoding) {
    $encoding = $this->get_canonized_encoding_name($encoding);

    global $g_utf8_converters;   
    if (isset($g_utf8_converters[$encoding])) {
      $vector = $g_utf8_converters[$encoding][0];
    } elseif (isset($this->_encodings[$encoding])) {
      $vector = $this->_encodings[$encoding];
    } else {
      return null;
    };

    for ($i = 0; $i <= 255; $i++) {
      if (!isset($vector[chr($i)])) {
        $vector[chr($i)] = 0xFFFF;
      };
    };

    return $vector;
  }

  function get_glyph_to_code_mapping($encoding) {
    $vector = $this->get_encoding_vector($encoding);

    $result = array();
    foreach ($vector as $code => $uccode) {
      if (isset($GLOBALS['g_unicode_glyphs'][$uccode])) {
        $result[$GLOBALS['g_unicode_glyphs'][$uccode]][] = $code;
      };
    };

    return $result;
  }

  function get_mapping($char) {
    if (!isset($this->_utf8_mapping)) {
      # $this->load_mapping(CACHE_DIR . 'utf8.mappings.dat');
      $this->load_mapping(HTML2PS_DIR."cache/utf8.mappings.dat");
    };

    if (!isset($this->_utf8_mapping[$char])) { 
      return null; 
    };
    return $this->_utf8_mapping[$char];
  }

  function get_next_utf8_char($raw_content, &$ptr) {
    if ((ord($raw_content[$ptr]) & 0xF0) == 0xF0) {
      $charlen = 4;
    } elseif ((ord($raw_content[$ptr]) & 0xE0) == 0xE0) {
      $charlen = 3;
    } elseif ((ord($raw_content[$ptr]) & 0xC0) == 0xC0) {
      $charlen = 2;
    } else {
      $charlen = 1;
    };
    
    $char = substr($raw_content,$ptr,$charlen);
    $ptr += $charlen;

    return $char;
  }

  function get_ps_encoding_vector($encoding) {
    $vector = $this->get_encoding_vector($encoding);

    $result = "/".$encoding." [ \n";
    for ($i=0; $i<256; $i++) {
      if ($i % 10 == 0) { $result .= "\n"; };

      // ! Note the order of array checking; optimizing interpreters may break this
      if (isset($vector[chr($i)]) && isset($GLOBALS['g_unicode_glyphs'][$vector[chr($i)]])) {
        $result .= " /".$GLOBALS['g_unicode_glyphs'][$vector[chr($i)]];
      } else {
        $result .= " /.notdef";
      };
    };
    $result .= " ] readonly def";

    return $result;
  }

  function is_custom_encoding($encoding) {
    return preg_match('/^custom\d+$/', $encoding);
  }

  function is_custom_encoding_full() {
    return count($this->_encodings[$this->get_current_custom_encoding_name()]) >= 256;
  }

  function load_mapping($mapping_file) {
    if (!is_readable($mapping_file)) {
      $this->generate_mapping($mapping_file);
    } else {
      $this->_utf8_mapping = unserialize(file_get_contents($mapping_file));
    };
  }

  /**
   * Create new custom  256-characters encoding vector.  Reserve first
   * 32 symbols for system use.
   *
   * Custom encoding vectors have names 'customX' when X stand for the
   * encoding index.
   */
  function new_custom_encoding_vector() {
    $initial_vector = array();
    for ($i = 0; $i <= 32; $i++) {
      $initial_vector[chr($i)] = chr($i);
    };
    $this->register_encoding(sprintf('custom%d', 
                                     $this->next_custom_vector_index()),
                             $initial_vector);
  }
  
  /**
   * Returns index for the next custom encoding 
   */
  function next_custom_vector_index() {
    return ++$this->_custom_vector_index;
  }

  function register_encoding($name, $vector) {
    $this->_encodings[$name] = $vector;
  }

  function to_utf8($word, $encoding) {
    $vector = $this->get_encoding_vector($encoding);
    
    $converted = '';
    for ($i=0, $size=strlen($word); $i < $size; $i++) {
      $converted .= code_to_utf8($vector[$word{$i}]);
    };

    return $converted;
  }

  function vector_to_glyphs($vector) {
    $result = array();

    foreach ($vector as $code => $ucs2) {      
      if (isset($GLOBALS['g_unicode_glyphs'][$ucs2])) {
        $result[$code] = $GLOBALS['g_unicode_glyphs'][$ucs2];
      } elseif ($ucs2 == 0xFFFF) {
        $result[$code] = ".notdef";
      } else {
        // Use "Unicode and Glyph Names" mapping from Adobe
        // http://partners.adobe.com/public/developer/opentype/index_glyph.html
        $result[$code] = sprintf("u%04X", $ucs2);
      };
    };

    return $result;
  }
}

global $g_manager_encodings;
$g_manager_encodings = new ManagerEncoding;
?>
