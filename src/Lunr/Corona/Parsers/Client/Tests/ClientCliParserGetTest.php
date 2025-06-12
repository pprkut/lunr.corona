<?php

/**
 * This file contains the ClientCliParserGetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client\Tests;

use Lunr\Corona\Parsers\Client\ClientCliParser;
use Lunr\Corona\Parsers\Client\ClientValue;
use Lunr\Corona\Parsers\Client\Tests\Helpers\MockClientEnum;
use Lunr\Corona\Tests\Helpers\MockRequestValue;
use RuntimeException;

/**
 * This class contains test methods for the ClientCliParser class.
 *
 * @backupGlobals enabled
 * @covers        Lunr\Corona\Parsers\Client\ClientCliParser
 */
class ClientCliParserGetTest extends ClientCliParserTestCase
{

    /**
     * Test that getRequestValueType() returns the correct type.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::getRequestValueType
     */
    public function testGetRequestValueType()
    {
        $this->assertEquals(ClientValue::class, $this->class->getRequestValueType());
    }

    /**
     * Test getting an unsupported value.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::get
     */
    public function testGetUnsupportedValue()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported request value type "Lunr\Corona\Tests\Helpers\MockRequestValue"');

        $this->class->get(MockRequestValue::Foo);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::get
     */
    public function testGetParsedClient()
    {
        $client = MockClientEnum::CommandLine;

        $this->setReflectionPropertyValue('client', $client);
        $this->setReflectionPropertyValue('clientInitialized', TRUE);

        $value = $this->class->get(ClientValue::Client);

        $this->assertEquals($client->value, $value);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::get
     */
    public function testGetParsedNullClient()
    {
        $this->setReflectionPropertyValue('client', NULL);
        $this->setReflectionPropertyValue('clientInitialized', TRUE);

        $value = $this->class->get(ClientValue::Client);

        $this->assertNull($value);
    }

    /**
     * Test getting a client when it's missing in the parsed CLI arguments.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::get
     */
    public function testGetClientWithMissingCliArgument()
    {
        $class = new ClientCliParser(MockClientEnum::class, []);

        $value = $class->get(ClientValue::Client);

        $this->assertNull($value);

        $property = $this->getReflectionProperty('client');

        $this->assertNull($property->getValue($class));
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::get
     */
    public function testGetClient()
    {
        $client = MockClientEnum::CommandLine;

        $value = $this->class->get(ClientValue::Client);

        $this->assertEquals($client->value, $value);
        $this->assertPropertySame('client', $client);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::getAsEnum
     */
    public function testGetParsedClientAsEnum()
    {
        $client = MockClientEnum::CommandLine;

        $this->setReflectionPropertyValue('client', $client);
        $this->setReflectionPropertyValue('clientInitialized', TRUE);

        $value = $this->class->getAsEnum(ClientValue::Client);

        $this->assertEquals($client, $value);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::getAsEnum
     */
    public function testGetParsedNullClientAsEnum()
    {
        $this->setReflectionPropertyValue('client', NULL);
        $this->setReflectionPropertyValue('clientInitialized', TRUE);

        $value = $this->class->getAsEnum(ClientValue::Client);

        $this->assertNull($value);
    }

    /**
     * Test getting a client when it's missing in the parsed CLI arguments.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::getAsEnum
     */
    public function testGetClientWithMissingCliArgumentAsEnum()
    {
        $class = new ClientCliParser(MockClientEnum::class, []);

        $value = $class->getAsEnum(ClientValue::Client);

        $this->assertNull($value);

        $property = $this->getReflectionProperty('client');

        $this->assertNull($property->getValue($class));
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientCliParser::getAsEnum
     */
    public function testGetClientAsEnum()
    {
        $client = MockClientEnum::CommandLine;

        $value = $this->class->getAsEnum(ClientValue::Client);

        $this->assertEquals($client, $value);
        $this->assertPropertySame('client', $client);
    }

}

?>
