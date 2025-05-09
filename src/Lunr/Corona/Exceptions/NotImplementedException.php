<?php

/**
 * This file contains the NotImplementedException class.
 *
 * SPDX-FileCopyrightText: Copyright 2018 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Exceptions;

use Exception;
use Lunr\Corona\HttpCode;

/**
 * Exception for the Not Implemented HTTP error (501).
 */
class NotImplementedException extends HttpException
{

    /**
     * Constructor.
     *
     * @param string         $message  Error message
     * @param int            $appCode  Application error code
     * @param Exception|null $previous The previously thrown exception
     */
    public function __construct(string $message = 'Not implemented!', int $appCode = 0, ?Exception $previous = NULL)
    {
        parent::__construct($message, HttpCode::NOT_IMPLEMENTED, $appCode, $previous);
    }

}

?>
