<?php

/**
 * This file contains the ClientApiKeyParserTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client\Tests;

use Lunr\Corona\Parsers\Client\ClientApiKeyParser;
use Lunr\Corona\Parsers\Client\Tests\Helpers\MockClientEnum;
use Lunr\Halo\LunrBaseTestCase;

/**
 * This class contains test methods for the ClientApiKeyParser class.
 *
 * @covers Lunr\Corona\Parsers\Client\ClientApiKeyParser
 */
abstract class ClientApiKeyParserTestCase extends LunrBaseTestCase
{

    /**
     * Instance of the tested class.
     * @var ClientApiKeyParser
     */
    protected ClientApiKeyParser $class;

    /**
     * Allowed test API keys.
     * @var array<string, BackedEnum&ParsedEnumValueInterface>
     */
    protected array $keys = [
        '9c531993fd2f4d81b7cd57c1cfcb323e' => MockClientEnum::CommandLine,
    ];

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        $this->class = new ClientApiKeyParser(MockClientEnum::class, $this->keys);

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
