<?php
# $Id: labelvars11.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing phplot - tick/data label variant formatting - case 11
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => 'Case 2: XT=both, XD=n/a',    # Title line 2
  'x' => True,              # Chart type, explicit X values or not
  'xt_pos' => 'both',       # X Tick Label position, NULL to skip
  'xd_pos' => NULL,         # X Data Label position, NULL to skip
  );
require 'labelvars.php';
