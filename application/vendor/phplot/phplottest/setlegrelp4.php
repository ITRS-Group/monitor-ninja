<?php
# $Id: setlegrelp4.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing legend relative position - case plot-4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align TL to plot TL, TTF7',           # Title part 2
  'lx' => 0, 'ly' => 0,         # Legend box fixed point, relative coords
  'relto' => 'plot',                 # Relative to: 'image' or 'plot'
  'bx' => 0, 'by' => 0,               # Base point, relative coords
  'ttfontsize' => 7,  # If not NULL, use TT font at this size
  );
require 'setlegrel.php';
