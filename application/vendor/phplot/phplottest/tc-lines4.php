<?php
# $Id: tc-lines4.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Truecolor Lines plot with alpha and no blending
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'noalphablend' => True,   # If true, disable alpha blending
  'alpha' => 60,           # Alpha adjustment for data colors, NULL to skip
  );
require 'tc-lines.php';
