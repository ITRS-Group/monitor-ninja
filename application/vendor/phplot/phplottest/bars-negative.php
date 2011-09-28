<?php
# $Id: bars-negative.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing phplot - Bars, negative values, as strings
require_once 'phplot.php';

$data = array(array('A', '10', '-5'), array('B', '-10', '5'));
$p = new PHPlot();
$p->SetTitle('Bars with negative string values');
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->SetPlotType('bars');
#$p->SetDataBorderColors('red');
#$p->SetShading(0);
#$p->SetXAxisPosition(0);
$p->DrawGraph();
