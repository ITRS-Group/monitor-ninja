<?php
/**
 * ezcGraphVectorTest
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
 * @version //autogen//
 * @subpackage Tests
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Tests for ezcGraph class.
 *
 * @package Graph
 * @subpackage Tests
 */
class ezcGraphVectorTest extends ezcTestCase
{
	public static function suite()
	{
		return new PHPUnit_Framework_TestSuite( "ezcGraphVectorTest" );
	}

    public function testCreateVector()
    {
        $vector = new ezcGraphVector( 1, 2 );

        $this->assertEquals(
            1,
            $vector->x
        );

        $this->assertEquals(
            2,
            $vector->y
        );
    }

    public function testCreateVectorFromCoordinate()
    {
        $vector = ezcGraphVector::fromCoordinate( new ezcGraphCoordinate( 1, 2 ) );

        $this->assertEquals(
            1,
            $vector->x
        );

        $this->assertEquals(
            2,
            $vector->y
        );
    }

    public function testVectorLength()
    {
        $vector = new ezcGraphVector( 1, 2 );

        $this->assertEquals(
            sqrt( 5 ),
            $vector->length()
        );
    }

    public function testUnifyVector()
    {
        $vector = new ezcGraphVector( 2, 0 );
        $result = $vector->unify();

        $this->assertEquals(
            1,
            $vector->x
        );

        $this->assertEquals(
            0,
            $vector->y
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }

    public function testUnifyNullVector()
    {
        $vector = new ezcGraphVector( 0, 0 );
        $result = $vector->unify();

        $this->assertEquals(
            0,
            $vector->x
        );

        $this->assertEquals(
            0,
            $vector->y
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }

    public function testVectorMultiplyScalar()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->scalar( 2 );

        $this->assertEquals(
            2,
            $vector->x
        );

        $this->assertEquals(
            4,
            $vector->y
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }

    public function testVectorRotateClockwise()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->rotateClockwise();

        $this->assertEquals(
            $result,
            new ezcGraphVector( -2, 1 )
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }

    public function testVectorRotateCounterClockwise()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->rotateCounterClockwise();

        $this->assertEquals(
            $result,
            new ezcGraphVector( 2, -1 )
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }

    public function testVectorMultiplyCoordinate()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->mul( new ezcGraphCoordinate( 3, 2 ) );

        $this->assertEquals(
            $result,
            7
        );
    }

    public function testVectorMultiplyVector()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->mul( new ezcGraphVector( 3, 2 ) );

        $this->assertEquals(
            $result,
            7
        );
    }

    public function testVectorAngleCoordinate()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->angle( new ezcGraphCoordinate( 3, 2 ) );

        $this->assertEquals(
            $result,
            0.51914611424652,
            'Wrong angle returned',
            .01
        );
    }

    public function testVectorAngleVector()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->angle( new ezcGraphVector( 3, 2 ) );

        $this->assertEquals(
            $result,
            0.51914611424652,
            'Wrong angle returned',
            .01
        );
    }

    public function testVectorAngleVectorZero()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->angle( new ezcGraphVector( 0, 0 ) );

        $this->assertSame(
            $result,
            false,
            'Expected false because no angle could be calculated.'
        );
    }

    public function testVectorAngle180Vector()
    {
        $vector = new ezcGraphVector( 1, 0 );
        $result = $vector->angle( new ezcGraphVector( -1, 0 ) );

        $this->assertEquals(
            $result,
            M_PI,
            'Wrong angle returned',
            .01
        );
    }

    public function testVectorAngle180Vector2()
    {
        $vector = new ezcGraphVector( 0, 1 );
        $result = $vector->angle( new ezcGraphVector( 0, -1 ) );

        $this->assertEquals(
            $result,
            M_PI,
            'Wrong angle returned',
            .01
        );
    }

    public function testVectorAddCoordinate()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->add( new ezcGraphCoordinate( 3, 2 ) );

        $this->assertEquals(
            $vector,
            new ezcGraphVector( 4, 4 )
        );
    }

    public function testVectorAddVector()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->add( new ezcGraphVector( 3, 2 ) );

        $this->assertEquals(
            $vector,
            new ezcGraphVector( 4, 4 )
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }

    public function testVectorSubCoordinate()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->sub( new ezcGraphCoordinate( 3, 2 ) );

        $this->assertEquals(
            $vector,
            new ezcGraphVector( -2, 0 )
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }

    public function testVectorSubVector()
    {
        $vector = new ezcGraphVector( 1, 2 );
        $result = $vector->sub( new ezcGraphVector( 3, 2 ) );

        $this->assertEquals(
            $vector,
            new ezcGraphVector( -2, 0 )
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }

    public function testVectorTransform()
    {
        $vector = new ezcGraphVector( 0, 0 );

        $result = $vector->transform( new ezcGraphRotation( -90, new ezcGraphCoordinate( 15, 15 ) ) );

        $this->assertEquals(
            $vector,
            new ezcGraphVector( 0, 30 ),
            'Vector transformation does not have the expected result',
            .0001
        );

        $this->assertEquals(
            $result,
            $vector,
            'Result should be the vector itself'
        );
    }
}

?>
