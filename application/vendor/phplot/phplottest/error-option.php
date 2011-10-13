<?php
# $Id: error-option.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot error test - bad option checking (bug in PHPlot<=5.0.4, inadequate check)
require_once 'phplot.php';

$data = array(
  array('A',  0,  1),
  array('B',  1,  2),
);

$p = new PHPlot(400,300);
$p->SetTitle('Error Tests');
$p->SetDataValues($data);
$p->SetDataType('data-data');
$p->SetPlotType('q');
$p->DrawGraph();
