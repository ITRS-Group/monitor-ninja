<?php
# $Id: stock4.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: "Stock market" plot, using error bars - 4
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => "\n(shape: tee, color: red, width: 5, size: 10)",   # Title part 2
  'EBColors' => 'red',      # ErrorBarColors: color or arran or NULL to omit
  'EBShape' => 'tee',       # ErrorBarShape: tee or line or NULL to omit
  'EBLWidth' => 5,          # ErrorBarLineWidth: integer or NULL to omit
  'EBSize' => 10,           # ErrorBarSize: integer pixels or NULL to omit
  );
require 'stock.php';
