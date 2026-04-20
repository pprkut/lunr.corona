<?php

/**
 * This file contains the CliRequestParserParseRequestTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Shadow\Tests;

use Lunr\Corona\HttpMethod;
use Lunr\Corona\Tests\Helpers\RequestParserDynamicRequestTestTrait;
use Psr\Log\LogLevel;

/**
 * Basic tests for the case of empty superglobals.
 *
 * @covers        Lunr\Shadow\CliRequestParser
 * @backupGlobals enabled
 */
class CliRequestParserParseRequestTest extends CliRequestParserTestCase
{

    use RequestParserDynamicRequestTestTrait;

    /**
     * Preparation work for the request tests.
     *
     * @param string $protocol  Protocol name
     * @param string $port      Port number
     * @param bool   $useragent Whether to include useragent information or not
     * @param string $key       Device useragent key
     *
     * @return void
     */
    protected function prepare_request_test($protocol = 'HTTP', $port = '80', $useragent = FALSE, $key = ''): void
    {
        $_SERVER['SCRIPT_FILENAME'] = '/full/path/to/index.php';

        $this->mockFunction('gethostname', fn() => 'Lunr');

        if ($useragent === TRUE)
        {
            $property = $this->getReflectionProperty('ast');
            $ast      = $property->getValue($this->class);

            $ast['useragent'] = [ 'UserAgent' ];

            if ($key != '')
            {
                $ast[$key] = [ 'Device UserAgent' ];
            }

            $property->setValue($this->class, $ast);
        }

        $this->mockedCalls = [
            'default_application_path' => [ 'default_application_path', '/full/path/to/' ],
            'default_controller'       => [ 'default_controller', 'DefaultController' ],
            'default_method'           => [ 'default_method', 'default_method' ],
        ];
    }

    /**
     * Preparation work for the request tests.
     *
     * @param bool $controller Whether to set a controller value
     * @param bool $method     Whether to set a method value
     * @param bool $override   Whether to override default values or not
     *
     * @return void
     */
    protected function prepare_request_data($controller = TRUE, $method = TRUE, $override = FALSE): void
    {
        if ($override === FALSE)
        {
            return;
        }

        $property = $this->getReflectionProperty('ast');
        $ast      = $property->getValue($this->class);

        if ($controller === TRUE)
        {
            $ast[$this->controller] = [ 'thecontroller' ];
        }

        if ($method === TRUE)
        {
            $ast[$this->method] = [ 'themethod' ];
        }

        $ast[$this->params] = [ 'parama', 'paramb' ];

        $property->setValue($this->class, $ast);
    }

    /**
     * Preparation work for the request tests.
     *
     * @param bool $controller Whether to set a controller value
     * @param bool $method     Whether to set a method value
     *
     * @return void
     */
    protected function prepare_request_data_with_slashes($controller = TRUE, $method = TRUE): void
    {
        $property = $this->getReflectionProperty('ast');
        $ast      = $property->getValue($this->class);

        if ($controller === TRUE)
        {
            $ast[$this->controller] = [ '/thecontroller//' ];
        }

        if ($method === TRUE)
        {
            $ast[$this->method] = [ '/themethod/' ];
        }

        $ast[$this->params] = [ '/parama/', '//paramb/' ];

        $property->setValue($this->class, $ast);
    }

    /**
     * Cleanup work for the request tests.
     *
     * @return void
     */
    private function cleanup_request_test(): void
    {
        $this->unmockFunction('gethostname');
    }

    /**
     * Unit Test Data Provider for possible controller key names.
     *
     * @return array $base Array of controller key names
     */
    public static function controllerKeyNameProvider(): array
    {
        $value   = [];
        $value[] = [ 'controller' ];
        $value[] = [ 'c' ];

        return $value;
    }

    /**
     * Unit Test Data Provider for possible method key names.
     *
     * @return array $base Array of method key names
     */
    public static function methodKeyNameProvider(): array
    {
        $value   = [];
        $value[] = [ 'method' ];
        $value[] = [ 'm' ];

        return $value;
    }

    /**
     * Unit Test Data Provider for possible parameter key names.
     *
     * @return array $base Array of parameter key names
     */
    public static function paramsKeyNameProvider(): array
    {
        $value   = [];
        $value[] = [ 'param' ];
        $value[] = [ 'params' ];
        $value[] = [ 'p' ];

        return $value;
    }

    /**
     * Unit Test Data Provider for possible verbosity key names.
     *
     * @return array $base Array of verbosity key names
     */
    public static function verbosityProvider(): array
    {
        $value   = [];
        $value[] = [ 'v', 1, LogLevel::NOTICE ];
        $value[] = [ 'v', 2, LogLevel::INFO ];
        $value[] = [ 'v', 3, LogLevel::DEBUG ];
        $value[] = [ 'verbose', 1, LogLevel::NOTICE ];
        $value[] = [ 'verbose', 2, LogLevel::INFO ];
        $value[] = [ 'verbose', 3, LogLevel::DEBUG ];

        return $value;
    }

    /**
     * Unit Test Data Provider for Device Useragent keys in $_SERVER.
     *
     * @return array $keys Array of array keys.
     */
    public static function deviceUserAgentKeyProvider(): array
    {
        $keys   = [];
        $keys[] = [ 'device_useragent' ];
        $keys[] = [ 'device-useragent' ];

        return $keys;
    }

    /**
     * Test that parse_request() unsets request data in the AST.
     *
     * @covers Lunr\Shadow\CliRequestParser::parse_request
     */
    public function testParseRequestRemovesRequestDataFromAst(): void
    {
        $this->prepare_request_test('HTTP', '80');
        $this->prepare_request_data(TRUE, TRUE, TRUE);

        $this->class->parse_request();

        $ast = $this->getReflectionPropertyValue('ast');

        $this->assertIsArray($ast);
        $this->assertCount(6, $ast);
        $this->assertArrayNotHasKey('controller', $ast);
        $this->assertArrayNotHasKey('c', $ast);
        $this->assertArrayNotHasKey('method', $ast);
        $this->assertArrayNotHasKey('m', $ast);
        $this->assertArrayNotHasKey('params', $ast);
        $this->assertArrayNotHasKey('param', $ast);
        $this->assertArrayNotHasKey('p', $ast);

        $this->cleanup_request_test();
    }

    /**
     * Test that parse_request() sets default http method.
     *
     * @covers Lunr\Shadow\CliRequestParser::parse_request
     */
    public function testParseRequestSetsHttpMethodWithLongOption(): void
    {
        $this->prepare_request_test();
        $this->prepare_request_data();

        $ast = $this->getReflectionPropertyValue('ast');

        $ast['action'] = [ 'POST' ];

        $this->setReflectionPropertyValue('ast', $ast);

        $request = $this->class->parse_request();

        $this->assertIsArray($request);
        $this->assertArrayHasKey('action', $request);
        $this->assertEquals(HttpMethod::POST, $request['action']);

        $this->cleanup_request_test();
    }

    /**
     * Test that parse_request() sets default http method.
     *
     * @covers Lunr\Shadow\CliRequestParser::parse_request
     */
    public function testParseRequestSetsHttpMethodWithShortOption(): void
    {
        $this->prepare_request_test();
        $this->prepare_request_data();

        $ast = $this->getReflectionPropertyValue('ast');

        $ast['x'] = [ 'POST' ];

        $this->setReflectionPropertyValue('ast', $ast);

        $request = $this->class->parse_request();

        $this->assertIsArray($request);
        $this->assertArrayHasKey('action', $request);
        $this->assertEquals(HttpMethod::POST, $request['action']);

        $this->cleanup_request_test();
    }

    /**
     * Test that parse_request() sets default http method.
     *
     * @param string           $key    Verbosity key name
     * @param int              $amount Amount of verbosity parameters passed
     * @param Psr\Log\LogLevel $level  Parsed verbosity level
     *
     * @dataProvider verbosityProvider
     * @covers       Lunr\Shadow\CliRequestParser::parse_request
     */
    public function testParseRequestSetsVerbosityLevel($key, $amount, $level): void
    {
        $this->prepare_request_test();
        $this->prepare_request_data();

        $ast = $this->getReflectionPropertyValue('ast');

        $ast[$key] = [];

        for ($i = $amount; $i > 0; $i--)
        {
            $ast[$key][] = FALSE;
        }

        $this->setReflectionPropertyValue('ast', $ast);

        $request = $this->class->parse_request();

        $this->assertIsArray($request);
        $this->assertArrayHasKey('verbosity', $request);
        $this->assertEquals($level, $request['verbosity']);

        $this->cleanup_request_test();
    }

}

?>
