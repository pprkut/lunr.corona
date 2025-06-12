<?php

/**
 * This file contains the ClientApiKeyParserGetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client\Tests;

use Lunr\Corona\Parsers\Client\ClientApiKeyParser;
use Lunr\Corona\Parsers\Client\ClientValue;
use Lunr\Corona\Parsers\Client\Tests\Helpers\MockClientEnum;
use Lunr\Corona\Tests\Helpers\MockArrayAccess;
use Lunr\Corona\Tests\Helpers\MockRequestValue;
use RuntimeException;

/**
 * This class contains test methods for the ClientApiKeyParser class.
 *
 * @backupGlobals enabled
 * @covers        Lunr\Corona\Parsers\Client\ClientApiKeyParser
 */
class ClientApiKeyParserGetTest extends ClientApiKeyParserTestCase
{

    /**
     * Test that getRequestValueType() returns the correct type.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::getRequestValueType
     */
    public function testGetRequestValueType()
    {
        $this->assertEquals(ClientValue::class, $this->class->getRequestValueType());
    }

    /**
     * Test getting an unsupported value.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::get
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
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::get
     */
    public function testGetParsedClient()
    {
        $version = MockClientEnum::CommandLine;

        $this->setReflectionPropertyValue('client', $version);
        $this->setReflectionPropertyValue('clientInitialized', TRUE);

        $value = $this->class->get(ClientValue::Client);

        $this->assertEquals($version->value, $value);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::get
     */
    public function testGetParsedNullClient()
    {
        $this->setReflectionPropertyValue('client', NULL);
        $this->setReflectionPropertyValue('clientInitialized', TRUE);

        $value = $this->class->get(ClientValue::Client);

        $this->assertNull($value);
    }

    /**
     * Test getting a client when it's missing in the header.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::get
     */
    public function testGetClientWithMissingHeader()
    {
        $value = $this->class->get(ClientValue::Client);

        $this->assertNull($value);
        $this->assertPropertySame('client', NULL);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::get
     */
    public function testGetClient()
    {
        $version = MockClientEnum::CommandLine;

        $_SERVER['HTTP_API_KEY'] = '9c531993fd2f4d81b7cd57c1cfcb323e';

        $value = $this->class->get(ClientValue::Client);

        $this->assertEquals($version->value, $value);
        $this->assertPropertySame('client', $version);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::get
     */
    public function testGetClientFromCustomHeader()
    {
        $version = MockClientEnum::CommandLine;

        $_SERVER['HTTP_X_API_KEY'] = '9c531993fd2f4d81b7cd57c1cfcb323e';

        $class = new ClientApiKeyParser(MockClientEnum::class, $this->keys, 'X-Api-Key');

        $value = $class->get(ClientValue::Client);

        $this->assertEquals($version->value, $value);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::get
     */
    public function testGetClientUsingArrayAccess()
    {
        $version = MockClientEnum::CommandLine;

        $keys = new MockArrayAccess($this->keys);

        $_SERVER['HTTP_API_KEY'] = '9c531993fd2f4d81b7cd57c1cfcb323e';

        $class = new ClientApiKeyParser(MockClientEnum::class, $keys);

        $value = $class->get(ClientValue::Client);

        $this->assertEquals($version->value, $value);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::getAsEnum
     */
    public function testGetParsedClientAsEnum()
    {
        $version = MockClientEnum::CommandLine;

        $this->setReflectionPropertyValue('client', $version);
        $this->setReflectionPropertyValue('clientInitialized', TRUE);

        $value = $this->class->getAsEnum(ClientValue::Client);

        $this->assertEquals($version, $value);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::getAsEnum
     */
    public function testGetParsedNullClientAsEnum()
    {
        $this->setReflectionPropertyValue('client', NULL);
        $this->setReflectionPropertyValue('clientInitialized', TRUE);

        $value = $this->class->getAsEnum(ClientValue::Client);

        $this->assertNull($value);
    }

    /**
     * Test getting a client when it's missing in the header.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::getAsEnum
     */
    public function testGetClientWithMissingHeaderAsEnum()
    {
        $value = $this->class->getAsEnum(ClientValue::Client);

        $this->assertNull($value);
        $this->assertPropertySame('client', NULL);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::getAsEnum
     */
    public function testGetClientAsEnum()
    {
        $version = MockClientEnum::CommandLine;

        $_SERVER['HTTP_API_KEY'] = '9c531993fd2f4d81b7cd57c1cfcb323e';

        $value = $this->class->getAsEnum(ClientValue::Client);

        $this->assertEquals($version, $value);
        $this->assertPropertySame('client', $version);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::getAsEnum
     */
    public function testGetClientFromCustomHeaderAsEnum()
    {
        $version = MockClientEnum::CommandLine;

        $_SERVER['HTTP_X_API_KEY'] = '9c531993fd2f4d81b7cd57c1cfcb323e';

        $class = new ClientApiKeyParser(MockClientEnum::class, $this->keys, 'X-Api-Key');

        $value = $class->getAsEnum(ClientValue::Client);

        $this->assertEquals($version, $value);
    }

    /**
     * Test getting a parsed client.
     *
     * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser::get
     */
    public function testGetClientUsingArrayAccessAsEnum()
    {
        $version = MockClientEnum::CommandLine;

        $keys = new MockArrayAccess($this->keys);

        $_SERVER['HTTP_API_KEY'] = '9c531993fd2f4d81b7cd57c1cfcb323e';

        $class = new ClientApiKeyParser(MockClientEnum::class, $keys);

        $value = $class->getAsEnum(ClientValue::Client);

        $this->assertEquals($version, $value);
    }

}

?>
