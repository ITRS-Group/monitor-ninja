<?php
# $Id: setlegrelt1.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing legend relative position - case title-1
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Align TL to title TR',           # Title part 2
  'lx' => 0, 'ly' => 0,         # Legend box fixed point, relative coords
  'relto' => 'title',                 # Relative to: 'image' or 'plot'
  'bx' => 1, 'by' => 0,               # Base point, relative coords
  );
require 'setlegrel.php';
