<?php

/**
 * This file contains the request value parser for the client sourced from a cli argument.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client;

use BackedEnum;
use Lunr\Corona\ParsedEnumValueInterface;
use Lunr\Corona\RequestEnumValueInterface;
use Lunr\Corona\RequestEnumValueParserInterface;
use Lunr\Corona\RequestValueInterface;
use Lunr\Shadow\CliParserInterface;
use RuntimeException;

/**
 * Request Value Parser for the client.
 *
 * @phpstan-import-type CliParameters from CliParserInterface
 */
class ClientCliParser implements RequestEnumValueParserInterface
{

    /**
     * The parsed client value as enum.
     * @var (BackedEnum&ParsedEnumValueInterface)|null
     */
    protected readonly ?BackedEnum $client;

    /**
     * Whether the client value has been initialized or not.
     * @var true
     */
    protected readonly bool $clientInitialized;

    /**
     * Parser CLI argument AST.
     * @var CliParameters
     */
    protected readonly array $params;

    /**
     * The name of the enum to use for client values.
     * @var class-string<BackedEnum&ParsedEnumValueInterface>
     */
    protected readonly string $enumName;

    /**
     * Constructor.
     *
     * @param class-string<BackedEnum&ParsedEnumValueInterface> $enumName The name of the enum to use for client values.
     * @param CliParameters                                     $params   Parsed CLI argument AST
     */
    public function __construct(?string $enumName, array $params)
    {
        $this->enumName = $enumName;
        $this->params   = $params;
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
        return ClientValue::class;
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
            ClientValue::Client => (isset($this->clientInitialized) ? $this->client : $this->parse())?->value,
            default             => throw new RuntimeException('Unsupported request value type "' . $key::class . '"'),
        };
    }

    /**
     * Get a request value as an enum.
     *
     * @param BackedEnum&RequestEnumValueInterface $key The identifier/name of the request value to get
     *
     * @return ?BackedEnum The requested value
     */
    public function getAsEnum(BackedEnum&RequestEnumValueInterface $key): ?BackedEnum
    {
        return match ($key) {
            ClientValue::Client => isset($this->clientInitialized) ? $this->client : $this->parse(),
            default             => throw new RuntimeException('Unsupported request value type "' . $key::class . '"'),
        };
    }

    /**
     * Parse the client value from the HTTP authorization header.
     *
     * @return BackedEnum|null The parsed client
     */
    protected function parse(): ?BackedEnum
    {
        $client = NULL;

        if (array_key_exists('client', $this->params))
        {
            $client = $this->params['client'][0];
        }

        $this->client = call_user_func_array([ $this->enumName, 'tryFromRequestValue' ], [ $client ]);

        $this->clientInitialized = TRUE;

        return $this->client;
    }

}

?>
