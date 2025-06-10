<?php

/**
 * This file contains the ClientCliParserTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client\Tests;

use Lunr\Corona\Parsers\Client\ClientCliParser;
use Lunr\Corona\Parsers\Client\Tests\Helpers\MockClientEnum;
use Lunr\Halo\LunrBaseTestCase;

/**
 * This class contains test methods for the ClientCliParser class.
 *
 * @covers Lunr\Corona\Parsers\Client\ClientCliParser
 */
abstract class ClientCliParserTestCase extends LunrBaseTestCase
{

    /**
     * Instance of the tested class.
     * @var ClientCliParser
     */
    protected ClientCliParser $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        $client = 'Command Line';

        $ast = [
            'client' => [
                $client,
            ]
        ];

        $this->class = new ClientCliParser(MockClientEnum::class, $ast);

        parent::baseSetUp($this->class);
    }

    /**
     * TestCase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->class);

        parent::tearDown();
    }

}

?>
