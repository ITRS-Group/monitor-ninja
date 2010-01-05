<?php

// Note that REAL cache should check for Last-Modified HTTP header at least;
// As I don't like idea of implementing the full-scaled HTTP protocol library
// and curl extension is very rare, this implementation of cache is very simple.
// Cache is cleared after the script finishes it work!

// Also, it can have problems with simultaneous access to the images.

// The class responsible for downloading and caching images
// as PHP does not support the static variables, we'll use a global variable
// containing all cached objects; note that cache consumes memory!
//
$GLOBALS['g_image_cache'] = array();

class html2psImage {
  var $_handle;
  var $_filename;
  var $_type;

  function html2psImage($handle, $filename, $type) {
    $this->_handle = $handle;
    $this->_filename = $filename;
    $this->_type = $type;
  }

  function get_filename() {
    return $this->_filename;
  }

  function get_handle() {
    return $this->_handle;
  }

  function get_type() {
    return $this->_type;
  }

  function sx() {
    if (!$this->_handle) {
      return 0;
    };

    return imagesx($this->_handle);
  }

  function sy() {
    if (!$this->_handle) {
      return 0;
    };

    return imagesy($this->_handle);
  }
}

class ImageFactory {
  // Static funcion; checks if given URL is already cached and either returns 
  // cached object or downloads the requested image
  //
  function get($url, &$pipeline) {
    global $g_config;
    if (!$g_config['renderimages']) { return null; };

    global $g_image_cache;

    // Check if this URL have been cached
    //
    if (isset($g_image_cache[$url])) {
      //      return do_image_open($g_image_cache[$url]);
      return $g_image_cache[$url];
    };

    // Download image; we should do it before we call do_image_open,
    // as it tries to open image file twice: first to determine image type 
    // and second to actually create the image - PHP url wrappers do no caching 
    // at all
    //
    $filename = ImageFactory::make_cache_filename($url);

    // REQUIRES: PHP 4.3.0+
    // we suppress warning messages, as missing image files will cause 'copy' to print 
    // several warnings
    // 
    // @TODO: change to fetcher class call
    //

#error_log("image url = $url");

    $data = $pipeline->fetch($url);

# error_log(print_r($data->headers,true));

    if (is_null($data)) {
      error_log("Cannot fetch image: ".$url);
      return null; 
    };

    $file = fopen($filename, 'wb');
    fwrite($file, $data->content);
    fclose($file);
    $pipeline->pop_base_url();
    
    // register it in the cached objects array
    //
    $handle = do_image_open($filename, $type);

#error_log("handle isset = ".($handle?"TRUE":"FALSE")." for $filename");
# error_log("Loading image=$filename type=$type");

    if ($handle) {
      $g_image_cache[$url] =& new html2psImage($handle,
                                        $filename,
                                        $type);
#		error_log("successfully loaded image=$filename type=$type");
    } else {
      $g_image_cache[$url] = null;
    };

    // return image
    //
    // return do_image_open($filename);
    return $g_image_cache[$url];
  }

  // Makes the filename to contain the cached version of URL
  // 
  function make_cache_filename($url) {
    // We cannot use the $url as an cache image name as it could be longer than 
    // allowed file name length (especially after escaping specialy symbols)
    // thus, we generate long almost random 32-character name using the md5 hash function
    //
	global $HTML2PS_CACHE_DIR;
    return $HTML2PS_CACHE_DIR.md5(time() + $url + rand());
  }

  // Checks if cache directory is available
  //
  function check_cache_dir() {
	global $HTML2PS_CACHE_DIR;
    // TODO: some cool easily understandable error message for the case 
    // image cache directory cannot be created or accessed
    
    // Check if $HTML2PS_CACHE_DIR exists
    //
    if (!is_dir($HTML2PS_CACHE_DIR)) {
      // Cache directory does not exists; try to create it (with read/write rightss for the owner only)
      //
      if (!mkdir($HTML2PS_CACHE_DIR, 0700)) { die("Cache directory '$HTML2PS_CACHE_DIR' cannot be created"); }
    };

    // Check if we can read and write to the $HTML2PS_CACHE_DIR
    //
    // Note that directory should have 'rwx' (7) permission, so the script will
    // be able to list directory contents; under Windows is_executable always returns false
    // for directories, so we need to drop this check in this case.
    //
    // A user's note for 'is_executable' function on PHP5:
    // "The change doesn't appear to be documented, so I thought I would mention it. 
    // In php5, as opposed to php4, you can no longer rely on is_executable to check the executable bit 
    // on a directory in 'nix. You can still use the first note's method to check if a directory is traversable:
    // @file_exists("adirectory/.");"
    // 
    if (!is_readable($HTML2PS_CACHE_DIR) ||
        !is_writeable($HTML2PS_CACHE_DIR) ||
        (!@file_exists($HTML2PS_CACHE_DIR.'.'))) {
      // omg. Cache directory exists, but useless
      //
      die("Check cache directory permissions; cannot either read or write to directory cache");
    };
    
    return;
  }

  // Clears the image cache (as we're neither implemented checking of Last-Modified HTTP header nor 
  // provided the means of limiting the cache size
  //
  // TODO: Will cause problems with simultaneous access to the same images  
  //
  function clear_cache() {
    foreach ($GLOBALS['g_image_cache'] as $key => $value) {
      if (!is_null($value)) {
        unlink($value->get_filename());
      };
    };
    $GLOBALS['g_image_cache'] = array();
  }
}
?>
