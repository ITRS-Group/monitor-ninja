<?php
# $Id: error-badcolor.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot error test - invalid color name
require_once 'phplot.php';
$data = array(array('a', 1, 1), array('b', 2, 3), array('c', 3, 5));
$plot = new PHPlot();
$plot->SetBackgroundColor('solid');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetPlotType('lines');
$plot->DrawGraph();
