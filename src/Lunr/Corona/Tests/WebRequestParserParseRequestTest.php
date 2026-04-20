<?php

/**
 * This file contains the WebRequestParserParseRequestTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Tests;

use Lunr\Corona\HttpMethod;
use Lunr\Corona\Tests\Helpers\RequestParserDynamicRequestTestTrait;

/**
 * Basic tests for the case of empty superglobals.
 *
 * @covers        Lunr\Corona\WebRequestParser
 * @backupGlobals enabled
 */
class WebRequestParserParseRequestTest extends WebRequestParserTestCase
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
        $this->mockFunction('gethostname', fn() => 'Lunr');

        $_SERVER['SCRIPT_NAME'] = '/path/to/index.php';

        $_SERVER['SCRIPT_FILENAME'] = '/full/path/to/index.php';

        if ($useragent !== TRUE)
        {
            return;
        }

        $_SERVER['HTTP_USER_AGENT'] = 'UserAgent';

        if ($key == '')
        {
            return;
        }

        $_SERVER[$key] = 'Device UserAgent';
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
        $map = [];

        if ($controller === TRUE)
        {
            $map[] = [ 'default_controller', 'DefaultController' ];
        }

        if ($method === TRUE)
        {
            $map[] = [ 'default_method', 'default_method' ];
        }

        $this->configuration->expects($this->any())
                            ->method('offsetGet')
                            ->willReturnMap($map);

        if ($override === FALSE)
        {
            return;
        }

        if ($controller === TRUE)
        {
            $_GET[$this->controller] = 'thecontroller';
        }

        if ($method === TRUE)
        {
            $_GET[$this->method] = 'themethod';
        }

        $_GET[$this->params . '1'] = 'parama';
        $_GET[$this->params . '2'] = 'paramb';
        $_GET['data']              = 'value';
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
        if ($controller === TRUE)
        {
            $_GET[$this->controller] = '/thecontroller//';
        }

        if ($method === TRUE)
        {
            $_GET[$this->method] = '/themethod/';
        }

        $_GET[$this->params . '1'] = '/parama/';
        $_GET[$this->params . '2'] = '//paramb/';
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
        $keys[] = [ 'HTTP_X_DEVICE_USER_AGENT' ];
        $keys[] = [ 'HTTP_X_ORIGINAL_USER_AGENT' ];
        $keys[] = [ 'HTTP_X_OPERAMINI_PHONE_UA' ];
        $keys[] = [ 'HTTP_X_SKYFIRE_PHONE' ];
        $keys[] = [ 'HTTP_X_BOLT_PHONE_UA' ];
        $keys[] = [ 'HTTP_DEVICE_STOCK_UA' ];
        $keys[] = [ 'HTTP_X_UCBROWSER_DEVICE_UA' ];

        return $keys;
    }

    /**
     * Test that parse_request() unsets request data in the $_GET super global variable.
     *
     * @covers Lunr\Corona\WebRequestParser::parse_request
     */
    public function testParseRequestRemovesRequestDataFromGet(): void
    {
        $this->prepare_request_test('HTTP', '80');
        $this->prepare_request_data(TRUE, TRUE, TRUE);

        $this->class->parse_request();

        $this->assertIsArray($_GET);
        $this->assertCount(1, $_GET);
        $this->assertArrayHasKey('data', $_GET);
        $this->assertEquals('value', $_GET['data']);

        $this->cleanup_request_test();
    }

    /**
     * Test that parse_request() sets $requestParsed to TRUE.
     *
     * @covers Lunr\Corona\WebRequestParser::parse_request
     */
    public function testParseRequestSetsRequestParsedTrue(): void
    {
        $this->prepare_request_test('HTTP', '80');
        $this->prepare_request_data(TRUE, TRUE, TRUE);

        $this->class->parse_request();

        $this->assertTrue($this->getReflectionPropertyValue('requestParsed'));

        $this->cleanup_request_test();
    }

    /**
     * Test that parse_request() returns an empty array if the data has already been parsed.
     *
     * @covers Lunr\Corona\WebRequestParser::parse_request
     */
    public function testParseRequestReturnsEmptyArrayIfAlreadyParsed(): void
    {
        $this->prepare_request_test('HTTP', '80');
        $this->prepare_request_data(TRUE, TRUE, TRUE);

        $this->setReflectionPropertyValue('requestParsed', TRUE);

        $this->assertArrayEmpty($this->class->parse_request());

        $this->cleanup_request_test();
    }

    /**
     * Test that parse_request() sets default http method.
     *
     * @covers Lunr\Corona\WebRequestParser::parse_request
     */
    public function testParseRequestSetsHttpMethod(): void
    {
        $this->prepare_request_test();
        $this->prepare_request_data();

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = $this->class->parse_request();

        $this->assertIsArray($request);
        $this->assertArrayHasKey('action', $request);
        $this->assertEquals(HttpMethod::POST, $request['action']);

        $this->cleanup_request_test();
    }

}

?>
