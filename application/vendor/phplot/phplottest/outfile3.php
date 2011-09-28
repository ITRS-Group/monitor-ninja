<?php
# $Id: outfile3.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Output file - 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (png, via constructor)",   # Title part 2
  'ftype' => 'png',         # file type: png gif jpg, NULL to omit.
  'useset' => False,         # True to use SetOutputFile, False to use constructor
  );
require 'outfile.php';
