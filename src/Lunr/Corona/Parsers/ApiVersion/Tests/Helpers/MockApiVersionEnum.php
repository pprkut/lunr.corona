<?php

/**
 * This file contains a mock API version enum.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\ApiVersion\Tests\Helpers;

use BackedEnum;
use Lunr\Corona\ParsedEnumValueInterface;
use Lunr\Corona\Parsers\ApiVersion\ApiVersionInterface;
use RuntimeException;

/**
 * Request Data Enums
 */
enum MockApiVersionEnum: int implements ParsedEnumValueInterface, ApiVersionInterface
{

    /**
     * Mock value.
     */
    case MOCK_1 = 1;

    /**
     * Mock value.
     */
    case MOCK_2 = 2;

    /**
     * Map scalar to an enum instance or NULL.
     *
     * This could just be an alias for BackedEnum::tryFrom(), but allows for more flexibility when needed.
     *
     * @param scalar|null $value The parsed request value
     *
     * @return BackedEnum&ParsedEnumValueInterface|null The requested value
     */
    public static function tryFromRequestValue(int|string|null $value): ?BackedEnum
    {
        return $value === NULL ? NULL : self::tryFrom($value);
    }

    /**
     * Verify that the API version is greater than, or equal to the passed API version.
     *
     * @param BackedEnum&ApiVersionInterface $apiVersion The API version to compare against
     *
     * @return bool Whether the API version meets the condition or not
     */
    public function isAtLeast(BackedEnum&ApiVersionInterface $apiVersion): bool
    {
        if ($apiVersion instanceof self)
        {
            return $this->value >= $apiVersion->value;
        }

        throw new RuntimeException('Tried comparing to a library API version!');
    }

    /**
     * Returns a string identifier for the scope of the API version.
     *
     * @return string API scope identifier
     */
    public function scope(): string
    {
        return 'Lunr.Corona.Mock';
    }

}

?>
