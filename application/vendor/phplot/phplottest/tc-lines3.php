<?php
# $Id: tc-lines3.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Truecolor Lines plot with alpha and gamma adjustment
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'gamma' => 0.5,           # Gamma adjust, NULL to skip
  'alpha' => 60,           # Alpha adjustment for data colors, NULL to skip
  );
require 'tc-lines.php';
