<?php

/**
 * This file contains the RequestTracingControllerTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Tests;

use Lunr\Corona\Parsers\TracingInfo\TracingInfoValue;
use Lunr\Corona\RequestValueParserInterface;

/**
 * Tests for getAsEnumting request values.
 *
 * @covers Lunr\Corona\Request
 */
class RequestTracingControllerTest extends RequestTestCase
{

    /**
     * Check that startChildSpan() starts a new span.
     *
     * @covers Lunr\Corona\Request::startChildSpan
     */
    public function testStartChildSpanWithExistingMock(): void
    {
        $parser = $this->getMockBuilder(RequestValueParserInterface::class)
                       ->getMock();

        $parsers = [
            TracingInfoValue::class => $parser,
        ];

        $this->setReflectionPropertyValue('parsers', $parsers);

        $mock = [
            [
                'controller' => 'test',
            ],
        ];

        $this->setReflectionPropertyValue('mock', $mock);

        $id = '00f067aa0ba902b7';

        $parser->expects($this->once())
               ->method('get')
               ->with(TracingInfoValue::SpanID)
               ->willReturn($id);

        $bytes = hex2bin('200c5938cbe14b58');

        $this->mockFunction('random_bytes', fn() => $bytes);

        $this->class->startChildSpan();

        $expected = [
            [
                TracingInfoValue::ParentSpanID->value => '00f067aa0ba902b7',
                TracingInfoValue::SpanID->value       => '200c5938cbe14b58',
                'controller'                          => 'test',
            ],
            [
                'controller' => 'test',
            ],
        ];

        $this->assertPropertyEquals('mock', $expected);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Check that startChildSpan() starts a new span.
     *
     * @covers Lunr\Corona\Request::startChildSpan
     */
    public function testStartChildSpan(): void
    {
        $parser = $this->getMockBuilder(RequestValueParserInterface::class)
                       ->getMock();

        $parsers = [
            TracingInfoValue::class => $parser,
        ];

        $this->setReflectionPropertyValue('parsers', $parsers);

        $id = '00f067aa0ba902b7';

        $parser->expects($this->once())
               ->method('get')
               ->with(TracingInfoValue::SpanID)
               ->willReturn($id);

        $bytes = hex2bin('200c5938cbe14b58');

        $this->mockFunction('random_bytes', fn() => $bytes);

        $this->class->startChildSpan();

        $expected = [
            [
                TracingInfoValue::ParentSpanID->value => '00f067aa0ba902b7',
                TracingInfoValue::SpanID->value       => '200c5938cbe14b58',
            ],
        ];

        $this->assertPropertyEquals('mock', $expected);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Check that stopChildSpan() stops the current span.
     *
     * @covers Lunr\Corona\Request::stopChildSpan
     */
    public function testStopChildSpanWithSingleMock(): void
    {
        $mock = [
            [
                TracingInfoValue::ParentSpanID->value => '00f067aa0ba902b7',
                TracingInfoValue::SpanID->value       => '200c5938cbe14b58',
                'controller'                          => 'test',
            ],
        ];

        $this->setReflectionPropertyValue('mock', $mock);

        $this->class->stopChildSpan();

        $expected = [];

        $this->assertPropertyEquals('mock', $expected);
    }

    /**
     * Check that stopChildSpan() stops the current span.
     *
     * @covers Lunr\Corona\Request::stopChildSpan
     */
    public function testStopChildSpanWithMultipleMocks(): void
    {
        $mock = [
            [
                TracingInfoValue::ParentSpanID->value => '00f067aa0ba902b7',
                TracingInfoValue::SpanID->value       => '200c5938cbe14b58',
                'controller'                          => 'test',
            ],
            [
                'controller' => 'test',
            ],
        ];

        $this->setReflectionPropertyValue('mock', $mock);

        $this->class->stopChildSpan();

        $expected = [
            [
                'controller' => 'test',
            ],
        ];

        $this->assertPropertyEquals('mock', $expected);
    }

    /**
     * Check that stopChildSpan() stops the current span.
     *
     * @covers Lunr\Corona\Request::stopChildSpan
     */
    public function testStopChildSpanWithNoMock(): void
    {
        $mock = [];

        $this->setReflectionPropertyValue('mock', $mock);

        $this->class->stopChildSpan();

        $this->assertPropertyEquals('mock', $mock);
    }

    /**
     * Test that getNewSpanId() returns a 16-hex span ID.
     *
     * @covers Lunr\Corona\Request::getNewSpanId
     */
    public function testGetNewSpanId(): void
    {
        $bytes = hex2bin('200c5938cbe14b58');

        $this->mockFunction('random_bytes', fn() => $bytes);

        $value = $this->class->getNewSpanId();

        $this->assertEquals('200c5938cbe14b58', $value);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Test that isValidSpanId() checks correctly for 16-hex span IDs.
     *
     * @covers Lunr\Corona\Request::isValidSpanId
     */
    public function testIsValidSpanId(): void
    {
        $this->assertTrue($this->class->isValidSpanId('200c5938cbe14b58'));
        $this->assertFalse($this->class->isValidSpanId('200c5938cbe14b58ad36022ab5c6bcc6'));
        $this->assertFalse($this->class->isValidSpanId('200c5938-cbe1-4b58-ad36-022ab5c6bcc6'));
        $this->assertFalse($this->class->isValidSpanId('0000000000000000'));
        $this->assertFalse($this->class->isValidSpanId('ZZZZZZZZZZZZZZZZ'));
    }

}

?>
