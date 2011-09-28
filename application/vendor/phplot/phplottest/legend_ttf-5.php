<?php
# $Id: legend_ttf-5.php 1001 2011-08-08 02:22:55Z lbayuk $
# SetLegendStyle - TrueTrype, increased line spacing
$tp = array(
  'suffix' => ' (TrueType, extra line spacing)',   # Title part 2
  'use_ttf' => True,       # True to use TTF text, False for GD
  'ttfsize' => 16,    # TrueType font size in points
  'text' => array(          # Legend array text, NULL to use built-in data.
     'Line 1 text', 'oxzz', 'Line 3', 'Ppq 4', 'Line 5'),
  'line_spacing' => 12,   # Text line spacing, NULL for default.
  );
require 'legend_--.php';
