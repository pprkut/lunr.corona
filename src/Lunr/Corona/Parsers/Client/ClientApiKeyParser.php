<?php

/**
 * This file contains the request value parser for the client sourced from an API key HTTP header.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\Client;

use ArrayAccess;
use BackedEnum;
use Lunr\Corona\ParsedEnumValueInterface;
use Lunr\Corona\RequestEnumValueInterface;
use Lunr\Corona\RequestEnumValueParserInterface;
use Lunr\Corona\RequestValueInterface;
use RuntimeException;

/**
 * Request Value Parser for the client.
 *
 * @phpstan-type ApiKeys array<string, BackedEnum&ParsedEnumValueInterface>|ArrayAccess<string, BackedEnum&ParsedEnumValueInterface>
 */
class ClientApiKeyParser implements RequestEnumValueParserInterface
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
     * The allowed API keys.
     * @var ApiKeys
     */
    protected readonly array|ArrayAccess $keys;

    /**
     * The name of the HTTP header holding the API key
     * @var string
     */
    protected readonly string $header;

    /**
     * The name of the enum to use for client values.
     * @var class-string<BackedEnum&ParsedEnumValueInterface>
     */
    protected readonly string $enumName;

    /**
     * Constructor.
     *
     * @param class-string<BackedEnum&ParsedEnumValueInterface> $enumName The name of the enum to use for client values.
     * @param ApiKeys                                           $keys     Allowed API keys
     * @param non-empty-string                                  $header   The name of the HTTP header holding the API key.
     */
    public function __construct(string $enumName, array|ArrayAccess $keys, string $header = 'Api-Key')
    {
        $this->enumName = $enumName;
        $this->keys     = $keys;
        $this->header   = 'HTTP_' . str_replace('-', '_', strtoupper($header));
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
        $key = NULL;

        if (array_key_exists($this->header, $_SERVER))
        {
            $key = $_SERVER[$this->header];
        }

        if (is_object($this->keys) && $this->keys->offsetExists($key) || is_array($this->keys) && array_key_exists($key, $this->keys))
        {
            $this->client = $this->keys[$key];
        }
        else
        {
            $this->client = call_user_func_array([ $this->enumName, 'tryFromRequestValue' ], [ NULL ]);
        }

        $this->clientInitialized = TRUE;

        return $this->client;
    }

}

?>
