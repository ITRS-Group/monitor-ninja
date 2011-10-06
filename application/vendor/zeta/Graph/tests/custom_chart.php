<?php
/**
 * File containing test code for the Graph component.
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
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */


class ezcCustomTestChart extends ezcGraphPieChart
{
    public function __construct( array $options = array() )
    {
        parent::__construct( $options );

        $this->driver = new ezcGraphSvgDriver();
        $this->renderer = new ezcGraphRenderer3d();

        $this->palette = new ezcGraphPaletteEzBlue();

        $this->title = 'Test chart';

        $this->data['testdata'] = new ezcGraphArrayDataSet( array(
            'foo' => 123,
            'bar' => 43,
            'blubb' => 453,
        ) );
    }
}

?>
