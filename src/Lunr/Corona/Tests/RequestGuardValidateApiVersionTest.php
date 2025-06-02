<?php

/**
 * This file contains the RequestGuardValidateApiVersionTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Tests;

use Lunr\Corona\Exceptions\BadRequestException;
use Lunr\Corona\Exceptions\PreconditionFailedException;
use Lunr\Corona\Parsers\ApiVersion\ApiVersionValue;
use Lunr\Corona\Parsers\ApiVersion\Tests\Helpers\MockApiVersionEnum;

/**
 * This class contains tests for the RequestGuard class
 *
 * @covers Lunr\Corona\RequestGuard
 */
class RequestGuardValidateApiVersionTest extends RequestGuardTestCase
{

    /**
     * Test that validateApiVersion() throws an exception when there is no api version.
     *
     * @covers Lunr\Corona\RequestGuard::validateApiVersion
     */
    public function testApiVersionIsInvalidWhenVersionIsMissing(): void
    {
        $this->request->expects($this->once())
                      ->method('getAsEnum')
                      ->with(ApiVersionValue::ApiVersion)
                      ->willReturn(NULL);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('No API version specified');

        try
        {
            $this->class->validateApiVersion(MockApiVersionEnum::MOCK_1);
        }
        catch (BadRequestException $e)
        {
            $this->assertEquals(ApiVersionValue::ApiVersion->value, $e->getDataKey());
            $this->assertNull($e->getDataValue());

            throw $e;
        }
    }

    /**
     * Test that validateApiVersion() throws an exception when the given api version is too low.
     *
     * @covers Lunr\Corona\RequestGuard::validateApiVersion
     */
    public function testApiVersionIsInvalidWhenVersionIsTooLow(): void
    {
        $this->request->expects($this->once())
                      ->method('getAsEnum')
                      ->with(ApiVersionValue::ApiVersion)
                      ->willReturn(MockApiVersionEnum::MOCK_1);

        $this->expectException(PreconditionFailedException::class);
        $this->expectExceptionMessage('API version is no longer supported!');

        $this->class->validateApiVersion(MockApiVersionEnum::MOCK_2);
    }

    /**
     * Test that validateApiVersion() throws no exception if API version is valid.
     *
     * @covers Lunr\Corona\RequestGuard::validateApiVersion
     */
    public function testValidateApiVersionIfValid(): void
    {
        $this->request->expects($this->once())
                      ->method('getAsEnum')
                      ->with(ApiVersionValue::ApiVersion)
                      ->willReturn(MockApiVersionEnum::MOCK_1);

        $this->class->validateApiVersion(MockApiVersionEnum::MOCK_1);
    }

}

?>
