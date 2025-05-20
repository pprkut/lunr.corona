<?php

/**
 * This file contains the client request value.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client;

use Lunr\Corona\RequestEnumValueInterface;

/**
 * Request Data Enums
 */
enum ClientValue: string implements RequestEnumValueInterface
{

    /**
     * API version
     */
    case Client = 'client';

}

?>
