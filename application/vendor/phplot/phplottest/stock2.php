<?php
# $Id: stock2.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: "Stock market" plot, using error bars - 2
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\n(bar shape: tee, width: 3)",   # Title part 2
  'EBShape' => 'tee',       # ErrorBarShape: tee or line or NULL to omit
  'EBLWidth' => 3,          # ErrorBarLineWidth: integer or NULL to omit
  );
require 'stock.php';
