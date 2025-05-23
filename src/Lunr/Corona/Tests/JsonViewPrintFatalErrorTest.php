<?php

/**
 * This file contains the JsonViewPrintFatalErrorTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Tests;

use Lunr\Corona\Parsers\Sapi\SapiValue;

/**
 * This class contains tests for the JsonView class.
 *
 * @covers Lunr\Corona\JsonView
 */
class JsonViewPrintFatalErrorTest extends JsonViewTestCase
{

    /**
     * Test that print_fatal_error() does not print an error page if there is no error.
     *
     * @covers Lunr\Corona\JsonView::print_fatal_error
     */
    public function testPrintFatalErrorPrintsNothingIfNoError(): void
    {
        $this->mockFunction('error_get_last', function () { return NULL; });

        $this->expectOutputString('');

        $this->class->print_fatal_error();

        $this->unmockFunction('error_get_last');
    }

    /**
     * Test that print_fatal_error() does not print an error page if there is no fatal error.
     *
     * @covers Lunr\Corona\JsonView::print_fatal_error
     */
    public function testPrintFatalErrorPrintsNothingIfErrorNotFatal(): void
    {
        $this->mockFunction('error_get_last', function () { return [ 'type' => 8, 'message' => 'Message', 'file' => 'index.php', 'line' => 2 ]; });

        $this->expectOutputString('');

        $this->class->print_fatal_error();

        $this->unmockFunction('error_get_last');
    }

    /**
     * Test that print_fatal_error() does prints a json object if there is a fatal error.
     *
     * @requires PHP 5.5.12
     * @covers   Lunr\Corona\JsonView::print_fatal_error
     */
    public function testPrintFatalErrorPrintsPrettyJson(): void
    {
        $this->mockFunction('error_get_last', function () { return [ 'type' => 1, 'message' => 'Message', 'file' => 'index.php', 'line' => 2 ]; });
        $this->mockFunction('header', function () {});
        $this->mockFunction('http_response_code', function () {});

        $this->request->expects($this->once())
                      ->method('get')
                      ->with(SapiValue::Sapi)
                      ->willReturn('cli');

        $this->expectOutputMatchesFile(TEST_STATICS . '/Corona/json_error.json');

        $this->class->print_fatal_error();

        $this->unmockFunction('error_get_last');
        $this->unmockFunction('header');
        $this->unmockFunction('http_response_code');
    }

    /**
     * Test that print_fatal_error() does prints a json object if there is a fatal error.
     *
     * @covers Lunr\Corona\JsonView::print_fatal_error
     */
    public function testPrintFatalErrorForWebPrintsJson(): void
    {
        $this->mockFunction('error_get_last', function () { return [ 'type' => 1, 'message' => 'Message', 'file' => 'index.php', 'line' => 2 ]; });
        $this->mockFunction('header', function () {});
        $this->mockFunction('http_response_code', function () {});

        $this->request->expects($this->once())
                      ->method('get')
                      ->with(SapiValue::Sapi)
                      ->willReturn('web');

        $this->expectOutputString('{"data":{},"status":{"code":500,"message":"Message in index.php on line 2"}}');

        $this->class->print_fatal_error();

        $this->unmockFunction('error_get_last');
        $this->unmockFunction('header');
        $this->unmockFunction('http_response_code');
    }

}

?>
