<?php

/**
 * This file contains the API version interface.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\ApiVersion;

use BackedEnum;

/**
 * API Version Interface.
 */
interface ApiVersionInterface
{

    /**
     * Verify that the API version is greater than, or equal to the passed API version.
     *
     * @param BackedEnum&ApiVersionInterface $apiVersion The API version to compare against
     *
     * @return bool Whether the API version meets the condition or not
     */
    public function isAtLeast(BackedEnum&ApiVersionInterface $apiVersion): bool;

    /**
     * Returns a string identifier for the scope of the API version.
     *
     * @return string API scope identifier
     */
    public function scope(): string;

}

?>
