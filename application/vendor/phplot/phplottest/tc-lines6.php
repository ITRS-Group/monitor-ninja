<?php
# $Id: tc-lines6.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Truecolor Lines plot with GIF output
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'alpha' => 50,           # Alpha adjustment for data colors, NULL to skip
  'output' => 'gif',       # Output format: png | gif | jpg
  );
require 'tc-lines.php';
