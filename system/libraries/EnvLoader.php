<?php
class EnvLoaderException extends Exception {}
class EnvLoader {
  /**
   * Extends Ninjas environment from the file at path.
   * The file should be in ini format, for example:
   * ENVVAR1=value1
   * ENVVAR2=value2
   *
   * Throws EnvLoaderException if the file could not be
   * read, or was badly formatted.
   *
   * @param string $path
   * @return bool
   * @throws EnvLoaderException
   */
  function load($path) {
    if (!is_readable($path)) {
      throw new EnvLoaderException("Env file '$path' is not readable");
    }

    $fh = fopen($path, 'r');

    if (!is_resource($fh)) {
      throw new EnvLoaderException("Failed to open env file '$path'");
    }

    while (($line = fgets($fh)) !== false) {
      if (putenv(trim($line)) === false) {
        throw new EnvLoaderException("Failed to put '$line' into environment");
      }
    }
    fclose($fh);
  }
}
