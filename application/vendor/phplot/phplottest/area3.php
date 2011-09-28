<?php
# $Id: area3.php 1001 2011-08-08 02:22:55Z lbayuk $
# Test: area plot with raised X axis
require_once 'phplot.php';
$data = array(
  array('A', 4, 3, 2, 1),
  array('B', 5, 4, 3, 2),
  array('C', 6, 5, 4, 3),
  array('D', 7, 6, 5, 4),
);
$plot = new PHPlot(800, 600);
$plot->SetTitle('Area plot with X axis raised to 3');
$plot->SetPlotType('area');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetXAxisPosition(3);
$plot->SetYTickIncrement(1);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->DrawGraph();
