<?php
# $Id: horzbar4.php 1001 2011-08-08 02:22:55Z lbayuk $
# Horizontal bars - case 4 options
# This is a parameterized test. See the script named at the bottom for details.
$tp = array_merge(array(
  'suffix' => "\nLong label, set area 0:25, TTF",       # Title part 2
  'nrows' => 3,                 # Number of bar groups
  'ncols' => 10,                # Number of bars per group
  'longlabel' => True,         # Data variation: long data label
  'ydatalabelpos' => 'plotleft',      # Y data label position (SetYDataLabelPos)
  'plotarea' => array(0, NULL, 25, NULL),       # Array[4] for SetPlotAreaWorld, or NULL.
  'ttf' => True,               # Use all TTF text
  ));
require 'horzbar.php';
