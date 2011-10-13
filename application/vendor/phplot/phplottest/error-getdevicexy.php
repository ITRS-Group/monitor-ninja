<?php
# $Id: error-getdevicexy.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot error test - cannot use coordinate translation before DrawGraph
require_once 'phplot.php';
$data = array( array('A', 1), array('B', 2));
$p = new PHPlot();
$p->SetDataType('text-data');
$p->SetPlotType('bars');
$p->SetDataValues($data);
# Error check: Can't call this yet:
list($x,$y) = $p->GetDeviceXY(0, 0);
$p->DrawGraph();
