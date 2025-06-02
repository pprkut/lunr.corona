<?php

/**
 * This file contains the RequestGuard class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona;

use BackedEnum;
use Lunr\Corona\Exceptions\BadRequestException;
use Lunr\Corona\Exceptions\PreconditionFailedException;
use Lunr\Corona\Parsers\ApiVersion\ApiVersionInterface;
use Lunr\Corona\Parsers\ApiVersion\ApiVersionValue;

/**
 * RequestGuard class.
 */
class RequestGuard
{

    /**
     * Shared instance of the Request class.
     * @var Request
     */
    protected readonly Request $request;

    /**
     * Constructor.
     *
     * @param Request $request Shared instance of the Request class
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        //NO-OP
    }

    /**
     * Check Requirements for a webservice request.
     *
     * @param BackedEnum&ApiVersionInterface $version The minimum required API version for the webservice.
     *
     * @return void
     */
    public function validateApiVersion(BackedEnum&ApiVersionInterface $version): void
    {
        /**
         * @var (BackedEnum&ApiVersionInterface)|null $api
         */
        $api = $this->request->getAsEnum(ApiVersionValue::ApiVersion);

        if ($api === NULL)
        {
            $e = new BadRequestException('No API version specified!');

            $e->setData(ApiVersionValue::ApiVersion->value, NULL);

            throw $e;
        }

        if (!$api->isAtLeast($version))
        {
            throw new PreconditionFailedException('API version is no longer supported!');
        }
    }

}

?>
