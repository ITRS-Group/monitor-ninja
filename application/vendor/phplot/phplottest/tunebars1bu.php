<?php
# $Id: tunebars1bu.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Bar chart tuning variables - full width unshaded bars
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'plot_type' => 'bars',
  'subtitle' => 'full width bars',
  'shading' => FALSE,
  'bar_extra_space' => 0,
  'group_frac_width' => 1.0,
  );
require 'tunebars.php';
