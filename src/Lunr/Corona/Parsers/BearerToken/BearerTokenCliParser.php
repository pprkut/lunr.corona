<?php

/**
 * This file contains the request value parser for the bearer token sourced from a cli argument.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\BearerToken;

use BackedEnum;
use Lunr\Corona\RequestValueInterface;
use Lunr\Corona\RequestValueParserInterface;
use Lunr\Shadow\CliParserInterface;
use RuntimeException;

/**
 * Request Value Parser for the bearer token.
 *
 * @phpstan-import-type CliParameters from CliParserInterface
 */
class BearerTokenCliParser implements RequestValueParserInterface
{

    /**
     * Parser CLI argument AST.
     * @var CliParameters
     */
    protected readonly array $params;

    /**
     * The parsed bearerToken value.
     * @var string|null
     */
    protected readonly ?string $bearerToken;

    /**
     * Whether the bearerToken value has been initialized or not.
     * @var true
     */
    protected readonly bool $bearerTokenInitialized;

    /**
     * Constructor.
     *
     * @param CliParameters $params Parsed CLI argument AST
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // no-op
    }

    /**
     * Return the request value type the parser handles.
     *
     * @return class-string The FQDN of the type enum the parser handles
     */
    public function getRequestValueType(): string
    {
        return BearerTokenValue::class;
    }

    /**
     * Get a request value.
     *
     * @param BackedEnum&RequestValueInterface $key The identifier/name of the request value to get
     *
     * @return string|null The requested value
     */
    public function get(BackedEnum&RequestValueInterface $key): ?string
    {
        return match ($key) {
            BearerTokenValue::BearerToken => isset($this->bearerTokenInitialized) ? $this->bearerToken : $this->parse(),
            default                       => throw new RuntimeException('Unsupported request value type "' . $key::class . '"'),
        };
    }

    /**
     * Parse the bearer token value from the HTTP authorization header.
     *
     * @return string|null The parsed bearer token
     */
    protected function parse(): ?string
    {
        $token = NULL;

        if (array_key_exists('bearer-token', $this->params))
        {
            $token = $this->params['bearer-token'][0];
        }

        $this->bearerToken = $token;

        $this->bearerTokenInitialized = TRUE;

        return $token;
    }

}

?>
