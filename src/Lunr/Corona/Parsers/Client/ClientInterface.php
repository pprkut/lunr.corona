<?php

/**
 * This file contains the Client interface.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client;

/**
 * Client Interface.
 */
interface ClientInterface
{

    /**
     * Whether the Client has global access to every API, without being required to be specifically listed.
     *
     * @return bool Whether the Client has global access or not
     */
    public function hasGlobalAccess(): bool;

}

?>
