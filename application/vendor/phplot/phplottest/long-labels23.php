<?php
# $Id: long-labels23.php 1001 2011-08-08 02:22:55Z lbayuk $
# X label size and angle test - TTF Angle 3
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ' TTF 10pt 60d',  # Title part 2
  'MaxLen' => 25,           # Max label length (int * 5)
  'angle' => 60,            # Label text angle, in degrees
  'TTF' => True,            # If True, use TrueType fonts, else GD fonts
  'FontSize' => 10,         # GD font size/TTF font point size, NULL to omit
  );
require 'long-labels.php';
