<?php
# $Id: legend_1ln.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: SetLegendStyle - single line, left with no colorboxes
$tp = array(
  'suffix' => ' (single line, left, no colorboxes)',   # Title part 2
  'textalign' => 'left',  # Text align argument
  'colorboxalign' => 'none', # Colorbox align
  'text' => 'Single line of text for the legend',
  );
require 'legend_--.php';
