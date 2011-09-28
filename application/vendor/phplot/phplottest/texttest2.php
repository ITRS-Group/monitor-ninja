<?php
# $Id: texttest2.php 1001 2011-08-08 02:22:55Z lbayuk $
# Text tests
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => " (TrueType font variations)", # Title part 2
  'use_ttf' => True,  # If true use TT fonts
  'use_gdf' => False,  # If true use GD fonts
  );
require 'texttest.php';
