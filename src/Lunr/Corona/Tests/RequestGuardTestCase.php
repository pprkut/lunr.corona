<?php

/**
 * This file contains the RequestGuardTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Tests;

use Lunr\Corona\Request;
use Lunr\Corona\RequestGuard;
use Lunr\Halo\LunrBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the RequestGuard class.
 *
 * @covers Lunr\Corona\RequestGuard
 */
abstract class RequestGuardTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of the Request class.
     * @var Request&MockObject
     */
    protected Request&MockObject $request;

    /**
     * Instance of the tested class.
     * @var RequestGuard
     */
    protected RequestGuard $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->request = $this->getMockBuilder(Request::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->class = new RequestGuard($this->request);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->request);
        unset($this->class);

        parent::tearDown();
    }

}

?>
