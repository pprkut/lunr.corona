<?php

/**
 * This file contains the RequestParserParseRequestTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Tests;

use Lunr\Corona\Tests\Helpers\RequestParserStaticRequestTestTrait;

/**
 * Basic tests for the case of empty superglobals.
 *
 * @covers        Lunr\Corona\RequestParser
 * @backupGlobals enabled
 */
class RequestParserParseRequestTest extends RequestParserTestCase
{

    use RequestParserStaticRequestTestTrait;

    /**
     * Mocked calls to Configuration
     * @var array
     */
    protected $mockedCalls = [];

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
        //NO-OP
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

}

?>
