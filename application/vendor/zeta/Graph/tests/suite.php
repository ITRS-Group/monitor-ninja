<?php
/**
 * ezcGraphSuite
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package Graph
 * @subpackage Tests
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
* Require test suites.
*/
require_once 'axis_centered_renderer_test.php';
require_once 'axis_exact_renderer_test.php';
require_once 'axis_boxed_renderer_test.php';
require_once 'axis_rotated_renderer_test.php';
require_once 'axis_rotated_boxed_renderer_test.php';
require_once 'axis_space_test.php';
require_once 'background_test.php';
require_once 'boundings_test.php';
require_once 'chart_test.php';
require_once 'color_test.php';
require_once 'dataset_average_test.php';
require_once 'dataset_numeric_test.php';
require_once 'dataset_test.php';
require_once 'date_axis_test.php';
require_once 'driver_flash_test.php';
require_once 'driver_gd_test.php';
require_once 'driver_cairo_test.php';
require_once 'driver_cairo_oo_test.php';
require_once 'driver_options_test.php';
require_once 'driver_svg_test.php';
require_once 'driver_svg_svg_font_test.php';
require_once 'element_options_test.php';
require_once 'font_test.php';
require_once 'image_map_test.php';
require_once 'labeled_axis_test.php';
require_once 'legend_test.php';
require_once 'line_test.php';
require_once 'logarithmical_axis_test.php';
require_once 'matrix_test.php';
require_once 'multiple_axis_test.php';
require_once 'numeric_axis_test.php';
require_once 'odometer_test.php';
require_once 'palette_test.php';
require_once 'pie_test.php';
require_once 'polynom_test.php';
require_once 'radar_axis_test.php';
require_once 'radar_chart.php';
require_once 'renderer_2d_test.php';
require_once 'renderer_2d_legacy_test.php';
require_once 'renderer_3d_test.php';
require_once 'horizontal_bar_chart_renderer.php';
require_once 'struct_test.php';
require_once 'text_test.php';
require_once 'tools_test.php';
require_once 'transformation_test.php';
require_once 'vector_test.php';

/**
* Test suite for Graph package.
*
* @package Graph
* @subpackage Tests
*/
class ezcGraphSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( "Graph" );

        $this->addTest( ezcGraphAxisCenteredRendererTest::suite() );
        $this->addTest( ezcGraphAxisExactRendererTest::suite() );
        $this->addTest( ezcGraphAxisBoxedRendererTest::suite() );
        $this->addTest( ezcGraphAxisRotatedRendererTest::suite() );
        $this->addTest( ezcGraphAxisRotatedBoxedRendererTest::suite() );
        $this->addTest( ezcGraphAxisSpaceTest::suite() );
        $this->addTest( ezcGraphBackgroundTest::suite() );
        $this->addTest( ezcGraphBoundingsTest::suite() );
        $this->addTest( ezcGraphChartTest::suite() );
        $this->addTest( ezcGraphColorTest::suite() );
        $this->addTest( ezcGraphDataSetAverageTest::suite() );
        $this->addTest( ezcGraphDataSetTest::suite() );
        $this->addTest( ezcGraphDateAxisTest::suite() );
        $this->addTest( ezcGraphDriverOptionsTest::suite() );
        $this->addTest( ezcGraphElementOptionsTest::suite() );
        $this->addTest( ezcGraphFlashDriverTest::suite() );
        $this->addTest( ezcGraphFontTest::suite() );
        $this->addTest( ezcGraphGdDriverTest::suite() );
        $this->addTest( ezcGraphCairoDriverTest::suite() );
        $this->addTest( ezcGraphCairoOODriverTest::suite() );
        $this->addTest( ezcGraphImageMapTest::suite() );
        $this->addTest( ezcGraphLabeledAxisTest::suite() );
        $this->addTest( ezcGraphLegendTest::suite() );
        $this->addTest( ezcGraphLineChartTest::suite() );
        $this->addTest( ezcGraphLogarithmicalAxisTest::suite() );
        $this->addTest( ezcGraphMatrixTest::suite() );
        $this->addTest( ezcGraphMultipleAxisTest::suite() );
        $this->addTest( ezcGraphNumericAxisTest::suite() );
        $this->addTest( ezcGraphNumericDataSetTest::suite() );
        $this->addTest( ezcGraphOdometerChartTest::suite() );
        $this->addTest( ezcGraphPaletteTest::suite() );
        $this->addTest( ezcGraphPieChartTest::suite() );
        $this->addTest( ezcGraphPolynomTest::suite() );
        $this->addTest( ezcGraphRadarChartAxisTest::suite() );
        $this->addTest( ezcGraphRadarChartTest::suite() );
        $this->addTest( ezcGraphRenderer2dTest::suite() );
        $this->addTest( ezcGraphRenderer2dLegacyTest::suite() );
        $this->addTest( ezcGraphRenderer3dTest::suite() );
        $this->addTest( ezcGraphHorizontalBarRendererTests::suite() );
        $this->addTest( ezcGraphStructTest::suite() );
        $this->addTest( ezcGraphSvgDriverTest::suite() );
        $this->addTest( ezcGraphSvgSvgFontDriverTest::suite() );
        $this->addTest( ezcGraphTextTest::suite() );
        $this->addTest( ezcGraphToolsTest::suite() );
        $this->addTest( ezcGraphTransformationTest::suite() );
        $this->addTest( ezcGraphVectorTest::suite() );
    }

    public static function suite()
    {
        return new ezcGraphSuite( "ezcGraphSuite" );
    }
}
?>
