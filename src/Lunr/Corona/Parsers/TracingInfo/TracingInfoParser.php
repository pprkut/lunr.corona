<?php

/**
 * This file contains the request value parser for the tracing info.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona\Parsers\TracingInfo;

use BackedEnum;
use Lunr\Corona\RequestValueInterface;
use Lunr\Corona\RequestValueParserInterface;
use RuntimeException;

/**
 * Request Value Parser for tracing info.
 */
class TracingInfoParser implements RequestValueParserInterface
{

    /**
     * W3C Trace Context traceparent header format: version-traceid-parentid-traceflags
     * @see https://www.w3.org/TR/trace-context/#traceparent-header-field-values
     */
    private const string TRACEPARENT_REGEX = '/^00-([a-f0-9]{32})-([a-f0-9]{16})-([a-f0-9]{2})$/';

    /**
     * The parsed trace ID.
     * @var string
     */
    protected readonly string $traceID;

    /**
     * The parsed span ID.
     * @var string
     */
    protected readonly string $spanID;

    /**
     * The parsed parent span ID from the incoming traceparent header.
     * @var ?string
     */
    protected readonly ?string $parentSpanID;

    /**
     * Whether the parentSpanID value has been initialized or not.
     * @var true
     */
    protected readonly bool $parentSpanIDInitialized;

    /**
     * The parsed trace flags from the incoming traceparent header.
     * @var string
     */
    protected readonly string $traceFlags;

    /**
     * Whether the traceFlags value has been initialized or not.
     * @var true
     */
    protected readonly bool $traceFlagsInitialized;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // no-op
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
        return TracingInfoValue::class;
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
        // Request ID is an alias for Trace ID
        return match ($key) {
            TracingInfoValue::TraceID,
            TracingInfoValue::RequestID    => $this->traceID ?? $this->parseTraceID(),
            TracingInfoValue::SpanID       => $this->spanID ?? $this->parseSpanID(),
            TracingInfoValue::ParentSpanID => isset($this->parentSpanIDInitialized) ? $this->parentSpanID : $this->parseParentSpanID(),
            TracingInfoValue::TraceFlags   => isset($this->traceFlagsInitialized) ? $this->traceFlags : $this->parseTraceFlags(),
            default                        => throw new RuntimeException('Unsupported request value type "' . $key::class . '"'),
        };
    }

    /**
     * Parse the traceparent header.
     *
     * @return void
     */
    protected function parseTraceparent(): void
    {
        if (array_key_exists('HTTP_TRACEPARENT', $_SERVER))
        {
            $header = $_SERVER['HTTP_TRACEPARENT'];

            if (preg_match(self::TRACEPARENT_REGEX, $header, $matches) === 1)
            {
                $parsedTraceID    = $matches[1];
                $parsedParentSpan = $matches[2];
                $parsedFlags      = $matches[3];

                // W3C Trace Context defines all-zero trace-id and parent-id as invalid.
                // These values indicate an absent or discarded context and must not be propagated.
                if ($parsedTraceID !== '00000000000000000000000000000000' && $parsedParentSpan !== '0000000000000000')
                {
                    $this->traceID      = $parsedTraceID;
                    $this->parentSpanID = $parsedParentSpan;
                    $this->traceFlags   = $parsedFlags;

                    $this->parentSpanIDInitialized = TRUE;
                    $this->traceFlagsInitialized   = TRUE;

                    return;
                }
            }
        }

        $this->parentSpanID = NULL;
        $this->traceFlags   = '01';

        $this->parentSpanIDInitialized = TRUE;
        $this->traceFlagsInitialized   = TRUE;
    }

    /**
     * Parse the parent span ID.
     *
     * @return string|null The parsed parent span ID
     */
    protected function parseParentSpanID(): ?string
    {
        $this->parseTraceparent();

        return $this->parentSpanID;
    }

    /**
     * Parse the trace flags.
     *
     * @return string The parsed trace flags
     */
    protected function parseTraceFlags(): string
    {
        $this->parseTraceparent();

        return $this->traceFlags;
    }

    /**
     * Parse the trace ID.
     *
     * @return string The parsed trace ID
     */
    protected function parseTraceId(): string
    {
        if (!isset($this->parentSpanIDInitialized))
        {
            $this->parseTraceparent();
        }

        if (isset($this->traceID))
        {
            return $this->traceID;
        }

        if (array_key_exists('REQUEST_ID', $_SERVER))
        {
            $this->traceID = $_SERVER['REQUEST_ID'];
        }
        else
        {
            $this->traceID = bin2hex(random_bytes(16));
        }

        return $this->traceID;
    }

    /**
     * Parse the span ID.
     *
     * @return string The parsed span ID
     */
    protected function parseSpanId(): string
    {
        $this->spanID = bin2hex(random_bytes(8));

        return $this->spanID;
    }

}

?>
