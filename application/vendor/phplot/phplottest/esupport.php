<?php
# Error test support routines
# $Id: esupport.php 1001 2011-08-08 02:22:55Z lbayuk $

# Error handler - report the error and exit:
function test_catch_exit($errno, $errstr, $errfile, $errline)
{
  fwrite(STDERR, "Caught error ($errno): $errstr\n");
  fwrite(STDERR, "  File: $errfile Line: $errline\n");
  fwrite(STDERR, "  Exit on error\n");
  exit(1);
}

# Error handler - report the error and return.
function test_catch_return($errno, $errstr, $errfile, $errline)
{
  fwrite(STDERR, "Caught error ($errno): $errstr\n");
  fwrite(STDERR, "  File: $errfile Line: $errline\n");
  fwrite(STDERR, "  Returning after error\n");
}
