<?php

/**
 * This file contains a mock API version enum.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client\Tests\Helpers;

use BackedEnum;
use Lunr\Corona\ParsedEnumValueInterface;

/**
 * Request Data Enums
 */
enum MockClientEnum: string implements ParsedEnumValueInterface
{

    /**
     * Mock value.
     */
    case CommandLine = 'Command Line';

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

}

?>
