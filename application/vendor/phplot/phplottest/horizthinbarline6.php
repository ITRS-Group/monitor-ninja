<?php
# $Id: horizthinbarline6.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test - thinbarline, horiz & vert - horiz <0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'horiz' => True,          # Vertical or horizontal
  'low' => -100,            # Bottom of data range
  'high' => -10,            # Top of data range
  );
require 'horizthinbarline.php';
