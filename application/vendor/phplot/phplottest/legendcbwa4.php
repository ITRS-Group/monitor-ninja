<?php
# $Id: legendcbwa4.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Legend color box width adjust, case 4
$cbwa = 5;
$tp = array(
  'suffix' => ": Color Box Width Adjust\nText left, Colorbox right, Width Adjust 3.0, Font TTF12",
  'textalign' => 'left',
  'colorboxalign' => 'right',
  'cbwa' => 3.0,
  'use_ttf' => TRUE,
  'ttfsize' => 12,
  );
require 'legend_--.php';
