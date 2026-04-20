<?php

/**
 * This file contains the TracingInfoParserGetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\TracingInfo\Tests;

use Lunr\Corona\Parsers\TracingInfo\TracingInfoValue;
use Lunr\Corona\Tests\Helpers\MockRequestValue;
use RuntimeException;

/**
 * This class contains test methods for the TracingInfoParser class.
 *
 * @backupGlobals enabled
 * @covers        Lunr\Corona\Parsers\TracingInfo\TracingInfoParser
 */
class TracingInfoParserGetTest extends TracingInfoParserTestCase
{

    /**
     * Test that getRequestValueType() returns the correct type.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::getRequestValueType
     */
    public function testGetRequestValueType(): void
    {
        $this->assertEquals(TracingInfoValue::class, $this->class->getRequestValueType());
    }

    /**
     * Test getting an unsupported value.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetUnsupportedValue(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported request value type "Lunr\Corona\Tests\Helpers\MockRequestValue"');

        $this->class->get(MockRequestValue::Foo);
    }

    /**
     * Test getting a parsed trace ID.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetParsedTraceID(): void
    {
        $traceID = '2cdfe3157e8649319704b5c6af3d0e80';

        $this->setReflectionPropertyValue('traceID', $traceID);

        $value = $this->class->get(TracingInfoValue::TraceID);

        $this->assertEquals($traceID, $value);
    }

    /**
     * Test getting a trace ID from the REQUEST_ID HTTP header.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetTraceIDFromHttpHeader(): void
    {
        $traceID = '2cdfe3157e8649319704b5c6af3d0e80';

        $_SERVER['REQUEST_ID'] = $traceID;

        $value = $this->class->get(TracingInfoValue::TraceID);

        $this->assertEquals($traceID, $value);
        $this->assertPropertySame('traceID', $traceID);
    }

    /**
     * Test getting a trace ID generates a new ID.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetTraceIDGeneratesNewID(): void
    {
        $bytes   = hex2bin('49f58d6f02244946acf9efcd63896263');
        $traceID = '49f58d6f02244946acf9efcd63896263';

        $this->mockFunction('random_bytes', fn() => $bytes);

        $value = $this->class->get(TracingInfoValue::TraceID);

        $this->assertEquals($traceID, $value);
        $this->assertPropertySame('traceID', $traceID);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Test getting a trace ID from a valid traceparent header.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetTraceIDFromTraceparentHeader(): void
    {
        $_SERVER['HTTP_TRACEPARENT'] = '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01';

        $value = $this->class->get(TracingInfoValue::TraceID);

        $this->assertEquals('4bf92f3577b34da6a3ce929d0e0e4736', $value);
        $this->assertPropertySame('traceID', '4bf92f3577b34da6a3ce929d0e0e4736');
    }

    /**
     * Test getting a parent span ID from a valid traceparent header.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetParentSpanIDFromTraceparentHeader(): void
    {
        $_SERVER['HTTP_TRACEPARENT'] = '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01';

        $value = $this->class->get(TracingInfoValue::ParentSpanID);

        $this->assertEquals('00f067aa0ba902b7', $value);
    }

    /**
     * Test getting trace flags from a valid traceparent header.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetTraceFlagsFromTraceparentHeader(): void
    {
        $_SERVER['HTTP_TRACEPARENT'] = '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-00';

        $value = $this->class->get(TracingInfoValue::TraceFlags);

        $this->assertEquals('00', $value);
    }

    /**
     * Test getting trace flags defaults to sampled when no traceparent is present.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetTraceFlagsDefaultsToSampled(): void
    {
        $value = $this->class->get(TracingInfoValue::TraceFlags);

        $this->assertEquals('01', $value);
    }

    /**
     * Test that traceparent takes priority over REQUEST_ID.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testTraceparentTakesPriorityOverRequestID(): void
    {
        $_SERVER['HTTP_TRACEPARENT'] = '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01';
        $_SERVER['REQUEST_ID']       = 'aaaabbbbccccddddeeee111122223333';

        $value = $this->class->get(TracingInfoValue::TraceID);

        $this->assertEquals('4bf92f3577b34da6a3ce929d0e0e4736', $value);
    }

    /**
     * Test that a malformed traceparent header is ignored.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testMalformedTraceparentIsIgnored(): void
    {
        $_SERVER['HTTP_TRACEPARENT'] = 'garbage-value';

        $bytes = hex2bin('49f58d6f02244946acf9efcd63896263');

        $this->mockFunction('random_bytes', fn() => $bytes);

        $value = $this->class->get(TracingInfoValue::TraceID);

        $this->assertEquals('49f58d6f02244946acf9efcd63896263', $value);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Test that a traceparent with all-zero trace ID is ignored.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testTraceparentAllZeroTraceIDIsIgnored(): void
    {
        $_SERVER['HTTP_TRACEPARENT'] = '00-00000000000000000000000000000000-00f067aa0ba902b7-01';

        $bytes = hex2bin('49f58d6f02244946acf9efcd63896263');

        $this->mockFunction('random_bytes', fn() => $bytes);

        $value = $this->class->get(TracingInfoValue::TraceID);

        $this->assertEquals('49f58d6f02244946acf9efcd63896263', $value);

        $parentValue = $this->class->get(TracingInfoValue::ParentSpanID);

        $this->assertNull($parentValue);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Test that a traceparent with all-zero parent span ID is ignored.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testTraceparentAllZeroParentSpanIDIsIgnored(): void
    {
        $_SERVER['HTTP_TRACEPARENT'] = '00-4bf92f3577b34da6a3ce929d0e0e4736-0000000000000000-01';

        $bytes = hex2bin('49f58d6f02244946acf9efcd63896263');

        $this->mockFunction('random_bytes', fn() => $bytes);

        $value = $this->class->get(TracingInfoValue::TraceID);

        $this->assertEquals('49f58d6f02244946acf9efcd63896263', $value);

        $parentValue = $this->class->get(TracingInfoValue::ParentSpanID);

        $this->assertNull($parentValue);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Test getting a parsed request ID.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetParsedRequestID(): void
    {
        $traceID = '2cdfe3157e8649319704b5c6af3d0e80';

        $this->setReflectionPropertyValue('traceID', $traceID);

        $value = $this->class->get(TracingInfoValue::RequestID);

        $this->assertEquals($traceID, $value);
    }

    /**
     * Test getting a request ID from the REQUEST_ID HTTP header.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetRequestIDFromHttpHeader(): void
    {
        $traceID = '2cdfe3157e8649319704b5c6af3d0e80';

        $_SERVER['REQUEST_ID'] = $traceID;

        $value = $this->class->get(TracingInfoValue::RequestID);

        $this->assertEquals($traceID, $value);
        $this->assertPropertySame('traceID', $traceID);
    }

    /**
     * Test getting a request ID generates a new ID.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetRequestIDGeneratesNewID(): void
    {
        $bytes   = hex2bin('49f58d6f02244946acf9efcd63896263');
        $traceID = '49f58d6f02244946acf9efcd63896263';

        $this->mockFunction('random_bytes', fn() => $bytes);

        $value = $this->class->get(TracingInfoValue::RequestID);

        $this->assertEquals($traceID, $value);
        $this->assertPropertySame('traceID', $traceID);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Test getting a parsed span ID.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetParsedSpanID(): void
    {
        $spanID = '9b00922000f349e6';

        $this->setReflectionPropertyValue('spanID', $spanID);

        $value = $this->class->get(TracingInfoValue::SpanID);

        $this->assertEquals($spanID, $value);
    }

    /**
     * Test getting a span ID generates a new ID.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetSpanIDGeneratesNewID(): void
    {
        $bytes  = hex2bin('49f58d6f02244946');
        $spanID = '49f58d6f02244946';

        $this->mockFunction('random_bytes', fn() => $bytes);

        $value = $this->class->get(TracingInfoValue::SpanID);

        $this->assertEquals($spanID, $value);
        $this->assertPropertySame('spanID', $spanID);

        $this->unmockFunction('random_bytes');
    }

    /**
     * Test getting a parent span ID when no traceparent is present.
     *
     * @covers Lunr\Corona\Parsers\TracingInfo\TracingInfoParser::get
     */
    public function testGetParentSpanID(): void
    {
        $value = $this->class->get(TracingInfoValue::ParentSpanID);

        $this->assertNull($value);
    }

}

?>
