<?php
# $Id: hvstackedbar7.php 1001 2011-08-08 02:22:55Z lbayuk $
# Horizontal Stacked Bars with Data Value Labels - Formatted
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'horiz' => True,      # True for horizontal bars, False for vertical
  'format' => 1,     # Label format mode: NULL, 1 (LabelType) 2, (DataLabelType)
  'formattype' => 'data', # Label format type: NULL, 'data', 'printf'
  'formatarg' => 2,  # Label format type argument
  );
require 'hvstackedbar.php';
