<?php

/**
 * This file contains the HttpMethodBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Tests;

use Lunr\Corona\HttpMethod;
use Lunr\Halo\LunrBaseTestCase;

/**
 * This class contains tests for the HttpMethod class.
 *
 * @covers Lunr\Corona\HttpMethod
 */
class HttpMethodBaseTest extends LunrBaseTestCase
{

    /**
     * Unit Test Data Provider for request value.
     *
     * @return array Request values.
     */
    public static function requestValueProvider(): array
    {
        $values = [];

        $values['int']              = [ 100, NULL ];
        $values['string-uppercase'] = [ 'GET', HttpMethod::Get ];
        $values['string-lowercase'] = [ 'get', HttpMethod::Get ];
        $values['null']             = [ NULL, NULL ];

        return $values;
    }

    /**
     * Test that tryFromRequestValue() instantiates correct enum.
     *
     * @param int|string|null $value    Request value
     * @param HttpMethod|null $expected Expected enum
     *
     * @dataProvider requestValueProvider
     * @covers       Lunr\Corona\HttpMethod::tryFromRequestValue
     */
    public function testTryFromRequestValue(int|string|null $value, ?HttpMethod $expected): void
    {
        $this->assertSame($expected, HttpMethod::tryFromRequestValue($value));
    }

}

?>
