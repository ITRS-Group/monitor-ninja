<?php
# $Id: setlegrelp3.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing legend relative position - case plot-3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align BL to plot BL',           # Title part 2
  'lx' => 0, 'ly' => 1,         # Legend box fixed point, relative coords
  'relto' => 'plot',                 # Relative to: 'image' or 'plot'
  'bx' => 0, 'by' => 1,               # Base point, relative coords
  );
require 'setlegrel.php';
