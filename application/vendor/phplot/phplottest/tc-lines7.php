<?php
# $Id: tc-lines7.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Truecolor Lines plot with JPEG output
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'alpha' => 50,           # Alpha adjustment for data colors, NULL to skip
  'output' => 'jpg',       # Output format: png | gif | jpg
  );
require 'tc-lines.php';
