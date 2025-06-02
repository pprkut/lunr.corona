<?php

/**
 * This file contains the RequestGuardBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Tests;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the RequestGuard class.
 *
 * @covers Lunr\Corona\RequestGuard
 */
class RequestGuardBaseTest extends RequestGuardTestCase
{

    /**
     * Test that the Request class is set correctly.
     */
    public function testRequestSetCorrectly(): void
    {
        $this->assertPropertySame('request', $this->request);
    }

}

?>
