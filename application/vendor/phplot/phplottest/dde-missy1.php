<?php
# $Id: dde-missy1.php 1001 2011-08-08 02:22:55Z lbayuk $
# Missing Y values in data-data-error plots: case 1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\nline, single, draw-broken",   # Title part 2
  'multiline' => False,     # True for 3 lines, false for 1 line.
  'plot-type' => 'lines',   # lines, points, linepoints
  'draw-broken' => True,   # See SetDrawBrokenLines
  );
require 'dde-missy.php';
