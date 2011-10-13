<?php
# $Id: labelvars12.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing phplot - tick/data label variant formatting - case 12
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Case 3: XT=n/a, XD=both',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => NULL,         # X Tick Label position, NULL to skip
  'xd_pos' => 'both',       # X Data Label position, NULL to skip
  );
require 'labelvars.php';
