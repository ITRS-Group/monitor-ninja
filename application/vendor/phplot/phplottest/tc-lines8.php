<?php
# $Id: tc-lines8.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Truecolor Lines plot with alpha, saved alpha
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'alpha' => 60,           # Alpha adjustment for data colors, NULL to skip
  'output' => 'png',         # Output format: png | gif | jpg
  'savealpha' => True,      # Save separate alpha channel?
  'noalphablend' => True,   # If true, disable alpha blending
  );
require 'tc-lines.php';
