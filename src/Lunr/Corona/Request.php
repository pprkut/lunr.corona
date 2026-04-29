<?php

/**
 * This file contains the request abstraction class.
 *
 * SPDX-FileCopyrightText: Copyright 2011 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Corona;

use BackedEnum;
use Lunr\Corona\Parsers\RouteInfo\RouteInfoValue;
use Lunr\Corona\Parsers\TracingInfo\TracingInfoValue;
use Lunr\Ticks\EventLogging\EventInterface;
use Lunr\Ticks\TracingControllerInterface;
use Lunr\Ticks\TracingInfoInterface;
use RuntimeException;

/**
 * Request abstraction class.
 * Manages access to $_POST, $_GET values, as well as
 * the request URL parameters
 *
 * @phpstan-import-type Tags from EventInterface
 * @phpstan-type UploadData array{
 *     name: string|array<string, string>,
 *     type: string|array<string, string>,
 *     tmp_name: string|array<string, string>,
 *     error: int|array<string, int>,
 *     size: int|array<string, int>,
 * }
 *
 * @property-read string      $action           The HTTP method used for the request
 * @property-read string|null $device_useragent The device specific user agent sent with the request
 * @property-read string|null $useragent        The user agent sent with the request
 * @property-read string      $host             The hostname of the server the script is running on
 * @property-read string      $controller       The controller requested
 * @property-read string      $method           The method requested of that controller
 * @property-read array       $params           The parameters for that method
 * @property-read string      $call             The call identifier, combining controller and method
 * @property-read string      $verbosity        Logging verbosity
 */
class Request implements TracingControllerInterface, TracingInfoInterface
{

    /**
     * Stored $_POST values
     * @var array<string,mixed>
     */
    protected readonly array $post;

    /**
     * Stored $_GET values
     * @var array<string,mixed>
     */
    protected readonly array $get;

    /**
     * Stored $_COOKIE values
     * @var array<string,mixed>
     */
    protected readonly array $cookie;

    /**
     * Stored $_SERVER values
     * @var array<string,mixed>
     */
    protected readonly array $server;

    /**
     * Request property data
     *
     * @var array<string, mixed>
     */
    protected array $request;

    /**
     * Stored $_FILES values
     * @var array<string,array<string,mixed>>
     */
    protected readonly array $files;

    /**
     * Stored php://input values
     * @var string
     */
    protected string $rawData;

    /**
     * Stored command line arguments
     * @var array<string,string|null>
     */
    protected readonly array $cliArgs;

    /**
     * Shared instance of the request parser.
     * @var RequestParserInterface
     */
    protected readonly RequestParserInterface $parser;

    /**
     * Set of registered request value parsers.
     * @var array<class-string,RequestValueParserInterface|RequestEnumValueParserInterface>
     */
    protected array $parsers;

    /**
     * The request values to mock.
     * @var list<array<string,mixed>>
     */
    private array $mock;

    /**
     * Whether to generate UUIDs as hex string or real UUIDs.
     * @var bool
     */
    protected readonly bool $uuidAsHexString;

    /**
     * Constructor.
     *
     * @param RequestParserInterface $parser          Shared instance of a Request Parser class
     * @param bool                   $uuidAsHexString Whether to generate UUIDs as hex string or real UUIDs
     */
    public function __construct($parser, $uuidAsHexString = TRUE)
    {
        $this->parser  = $parser;
        $this->parsers = [];

        $this->request = $parser->parse_request();
        $this->server  = $parser->parse_server();
        $this->post    = $parser->parse_post();
        $this->get     = $parser->parse_get();
        $this->cookie  = $parser->parse_cookie();
        $this->files   = $parser->parse_files();
        $this->cliArgs = $parser->parse_command_line_arguments();
        $this->rawData = '';

        $this->mock = [];

        $this->uuidAsHexString = $uuidAsHexString;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // Intentionally not unsetting request value properties, since
        // that may break access to them during PHP shutdown.
    }

    /**
     * Get access to certain private attributes.
     *
     * This gives access to the request keys.
     *
     * @param string $name Attribute name
     *
     * @return mixed $return Value of the chosen attribute
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->request))
        {
            if (!empty($this->mock) && array_key_exists($name, $this->mock[0]))
            {
                return $this->mock[0][$name];
            }

            return $this->request[$name];
        }

        return NULL;
    }

    /**
     * Get trace ID the event belongs to.
     *
     * @return string|null Trace ID
     */
    public function getTraceId(): ?string
    {
        return $this->get(TracingInfoValue::TraceID);
    }

    /**
     * Get span ID the event belongs to.
     *
     * @return string|null Span ID
     */
    public function getSpanId(): ?string
    {
        return $this->get(TracingInfoValue::SpanID);
    }

    /**
     * Get span ID of the parent the event belongs to.
     *
     * @return string|null Parent span ID
     */
    public function getParentSpanId(): ?string
    {
        return $this->get(TracingInfoValue::ParentSpanID);
    }

    /**
     * Get a new ID that can be used as a span ID.
     *
     * @return string New span ID
     */
    public function getNewSpanId(): string
    {
        $uuid = uuid_create();

        if ($this->uuidAsHexString)
        {
            $uuid = str_replace('-', '', $uuid);
        }

        return $uuid;
    }

    /**
     * Check whether a given string is valid for use as a span ID.
     *
     * @param string $id String to verify
     *
     * @return bool Whether the string is valid for use as a span ID or not
     */
    public function isValidSpanId(string $id): bool
    {
        if ($this->uuidAsHexString)
        {
            $regex = '/^[a-f0-9]{32}$/i';
        }
        else
        {
            $regex = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i';
        }

        return (bool) preg_match($regex, $id);
    }

    /**
     * Get tags that are specific to the current span.
     *
     * @return Tags Indexed metadata about the current span
     */
    public function getSpanSpecificTags(): array
    {
        if (!isset($this->parsers[RouteInfoValue::class]))
        {
            return [];
        }

        return [
            'controller' => $this->get(RouteInfoValue::Target),
            'route'      => $this->get(RouteInfoValue::Name),
            'routeGroup' => $this->get(RouteInfoValue::Group),
        ];
    }

    /**
     * Get a request value.
     *
     * @param BackedEnum&RequestValueInterface $key The identifier/name of the request value to get
     *
     * @return scalar The requested value
     */
    public function get(BackedEnum&RequestValueInterface $key): bool|float|int|string|null
    {
        if (!empty($this->mock) && array_key_exists($key->value, $this->mock[0]))
        {
            if (is_scalar($this->mock[0][$key->value]) || is_null($this->mock[0][$key->value]))
            {
                return $this->mock[0][$key->value];
            }

            if ($this->mock[0][$key->value] instanceof BackedEnum)
            {
                return $this->mock[0][$key->value]->value;
            }
        }

        if (array_key_exists($key->value, $this->request))
        {
            return $this->request[$key->value];
        }

        if (!isset($this->parsers[$key::class]))
        {
            throw new RuntimeException('No parser registered for requested value ("' . $key->value . '")!');
        }

        $this->request[$key->value] = $this->parsers[$key::class]->get($key);

        return $this->request[$key->value];
    }

    /**
     * Get a request value as enum.
     *
     * @param BackedEnum&RequestEnumValueInterface $key The identifier/name of the request value to get
     *
     * @return BackedEnum|null The requested value
     */
    public function getAsEnum(BackedEnum&RequestEnumValueInterface $key): ?BackedEnum
    {
        if (!empty($this->mock) && array_key_exists($key->value, $this->mock[0])
            && $this->mock[0][$key->value] instanceof BackedEnum && $this->mock[0][$key->value] instanceof ParsedEnumValueInterface
        )
        {
            return $this->mock[0][$key->value];
        }

        if (!isset($this->parsers[$key::class]))
        {
            throw new RuntimeException('No parser registered for requested value ("' . $key->value . '")!');
        }

        if (!$this->parsers[$key::class] instanceof RequestEnumValueParserInterface)
        {
            throw new RuntimeException($key::class . ' is not a valid parser for enum request values!');
        }

        return $this->parsers[$key::class]->getAsEnum($key);
    }

    /**
     * Register a request value parser.
     *
     * @param RequestValueParserInterface $parser A request value parser
     *
     * @return void
     */
    public function registerParser(RequestValueParserInterface $parser): void
    {
        $this->parsers[$parser->getRequestValueType()] = $parser;
    }

    /**
     * Start a new child span.
     *
     * @return void
     */
    public function startChildSpan(): void
    {
        $requestValues = [];

        $requestValues[TracingInfoValue::ParentSpanID->value] = $this->get(TracingInfoValue::SpanID);
        $requestValues[TracingInfoValue::SpanID->value]       = $this->getNewSpanId();

        if (!empty($this->mock))
        {
            $this->setMockValues($this->mock[0]);
        }

        $this->addMockValues($requestValues);
    }

    /**
     * Stop the current child span, returning to the scope of the parent.
     *
     * @return void
     */
    public function stopChildSpan(): void
    {
        array_shift($this->mock);
    }

    /**
     * Override request values detected from the request parser.
     * Replace all previous mock values.
     *
     * @deprecated Use setMockValues() instead
     *
     * @param array $values Array of key value pairs holding mocked request values
     *
     * @return void
     */
    public function set_mock_values(array $values): void
    {
        $this->setMockValues($values);
    }

    /**
     * Override request values detected from the request parser.
     * Replace all previous mock values.
     *
     * @param array $values Array of key value pairs holding mocked request values
     *
     * @return void
     */
    public function setMockValues(array $values): void
    {
        array_unshift($this->mock, $values);
    }

    /**
     * Override request values detected from the request parser.
     * Keep previous mock values and replace individual keys.
     *
     * @deprecated Use addMockValues() instead
     *
     * @param array $values Array of key value pairs holding mocked request values
     *
     * @return void
     */
    public function add_mock_values(array $values): void
    {
        $this->addMockValues($values);
    }

    /**
     * Override request values detected from the request parser.
     * Keep previous mock values and replace individual keys.
     *
     * @param array $values Array of key value pairs holding mocked request values
     *
     * @return void
     */
    public function addMockValues(array $values): void
    {
        if (empty($this->mock))
        {
            $this->setMockValues($values);
            return;
        }

        foreach ($values as $key => $value)
        {
            $this->mock[0][$key] = $value;
        }
    }

    /**
     * Returns a CLI option array of value(s).
     *
     * @deprecated Use getOptionData() instead
     *
     * @param string $key Key for the value to retrieve
     *
     * @return mixed $return The value of the key or NULL if not found
     */
    public function get_option_data(string $key): mixed
    {
        return $this->getOptionData($key);
    }

    /**
     * Returns a CLI option array of value(s).
     *
     * @param string $key Key for the value to retrieve
     *
     * @return mixed $return The value of the key or NULL if not found
     */
    public function getOptionData(string $key): mixed
    {
        return $this->getData($key, RequestData::CliArgument);
    }

    /**
     * Returns all CLI options.
     *
     * @return array $return The option and the arguments of the request
     */
    public function get_all_options(): array
    {
        return $this->getAllOptions();
    }

    /**
     * Returns all CLI options.
     *
     * @deprecated Use getAllOptions() instead
     *
     * @return array $return The option and the arguments of the request
     */
    public function getAllOptions(): array
    {
        return $this->getData(type: RequestData::CliOption);
    }

    /**
     * Negotiate & retrieve the client's preferred content type.
     *
     * @deprecated Use getAcceptFormat() instead
     *
     * @param array $supported Array containing the supported content types
     *
     * @return string|null $return The best match of the preferred content types or NULL
     *                       if there are no supported types or the header is not set
     */
    public function get_accept_format(array $supported = []): ?string
    {
        return $this->getAcceptFormat($supported);
    }

    /**
     * Negotiate & retrieve the client's preferred content type.
     *
     * @param array $supported Array containing the supported content types
     *
     * @return string|null $return The best match of the preferred content types or NULL
     *                       if there are no supported types or the header is not set
     */
    public function getAcceptFormat(array $supported = []): ?string
    {
        return $this->parser->parse_accept_format($supported);
    }

    /**
     * Negotiate & retrieve the clients preferred language.
     *
     * @deprecated Use getAcceptLanguage() instead
     *
     * @param array $supported Array containing the supported languages
     *
     * @return string|null $return The best match of the preferred languages or NULL if
     *                       there are no supported languages or the header is not set
     */
    public function get_accept_language(array $supported = []): ?string
    {
        return $this->getAcceptLanguage($supported);
    }

    /**
     * Negotiate & retrieve the clients preferred language.
     *
     * @param array $supported Array containing the supported languages
     *
     * @return string|null $return The best match of the preferred languages or NULL if
     *                       there are no supported languages or the header is not set
     */
    public function getAcceptLanguage(array $supported = []): ?string
    {
        return $this->parser->parse_accept_language($supported);
    }

    /**
     * Negotiate & retrieve the clients preferred charset.
     *
     * @param array $supported Array containing the supported charsets
     *
     * @return string|null $return The best match of the preferred charsets or NULL if
     *                       there are no supported charsets or the header is not set
     */
    public function get_accept_charset(array $supported = []): ?string
    {
        return $this->getAcceptCharset($supported);
    }

    /**
     * Negotiate & retrieve the clients preferred charset.
     *
     * @deprecated Use getAcceptCharset() instead
     *
     * @param array $supported Array containing the supported charsets
     *
     * @return string|null $return The best match of the preferred charsets or NULL if
     *                       there are no supported charsets or the header is not set
     */
    public function getAcceptCharset(array $supported = []): ?string
    {
        return $this->parser->parse_accept_charset($supported);
    }

    /**
     * Retrieve a stored GET value.
     *
     * @deprecated Use getGetData() instead
     *
     * @param string|null $key Key for the value to retrieve
     *
     * @return string|string[]|null The value of the key, all GET values if no key is provided or NULL if not found.
     */
    public function get_get_data(?string $key = NULL): string|array|null
    {
        return $this->getGetData($key);
    }

    /**
     * Retrieve a stored GET value.
     *
     * @param string|null $key Key for the value to retrieve
     *
     * @return string|string[]|null The value of the key, all GET values if no key is provided or NULL if not found.
     */
    public function getGetData(?string $key = NULL): string|array|null
    {
        return $this->getData($key, RequestData::Get);
    }

    /**
     * Retrieve a stored POST value.
     *
     * @deprecated Use getPostData() instead
     *
     * @param string|null $key Key for the value to retrieve
     *
     * @return string|string[]|null The value of the key, all POST values if no key is provided or NULL if not found.
     */
    public function get_post_data(?string $key = NULL): string|array|null
    {
        return $this->getPostData($key);
    }

    /**
     * Retrieve a stored POST value.
     *
     * @param string|null $key Key for the value to retrieve
     *
     * @return string|string[]|null The value of the key, all POST values if no key is provided or NULL if not found.
     */
    public function getPostData(?string $key = NULL): string|array|null
    {
        return $this->getData($key, RequestData::Post);
    }

    /**
     * Retrieve a stored SERVER value.
     *
     * @deprecated Use getServerData() instead
     *
     * @param string $key Key for the value to retrieve
     *
     * @return float|int|string|string[]|null The value of the key or NULL if not found
     */
    public function get_server_data(string $key): float|int|string|array|null
    {
        return $this->getServerData($key);
    }

    /**
     * Retrieve a stored SERVER value.
     *
     * @param string $key Key for the value to retrieve
     *
     * @return float|int|string|string[]|null The value of the key or NULL if not found
     */
    public function getServerData(string $key): float|int|string|array|null
    {
        return $this->getData($key, RequestData::Server);
    }

    /**
     * Retrieve a stored HTTP Header from the SERVER value.
     *
     * @deprecated Use getHttpHeaderData() instead
     *
     * @param string $key Key for the value to retrieve
     *
     * @return string|null The value of the key or NULL if not found
     */
    public function get_http_header_data(string $key): string|null
    {
        return $this->getHttpHeaderData($key);
    }

    /**
     * Retrieve a stored HTTP Header from the SERVER value.
     *
     * @param string $key Key for the value to retrieve
     *
     * @return string|null The value of the key or NULL if not found
     */
    public function getHttpHeaderData(string $key): string|null
    {
        return $this->getData($key, RequestData::Header);
    }

    /**
     * Retrieve a stored COOKIE value.
     *
     * @deprecated Use getCookieData() instead
     *
     * @param string $key Key for the value to retrieve
     *
     * @return string|string[]|null The value of the key or NULL if not found
     */
    public function get_cookie_data(string $key): string|array|null
    {
        return $this->getCookieData($key);
    }

    /**
     * Retrieve a stored COOKIE value.
     *
     * @param string $key Key for the value to retrieve
     *
     * @return string|string[]|null The value of the key or NULL if not found
     */
    public function getCookieData(string $key): string|array|null
    {
        return $this->getData($key, RequestData::Cookie);
    }

    /**
     * Retrieve a stored FILE value.
     *
     * @deprecated Use getFilesData() instead
     *
     * @param string $key Key for the value to retrieve
     *
     * @return UploadData|null The value of the key or NULL if not found
     */
    public function get_files_data(string $key): ?array
    {
        return $this->getFilesData($key);
    }

    /**
     * Retrieve a stored FILE value.
     *
     * @param string $key Key for the value to retrieve
     *
     * @return UploadData|null The value of the key or NULL if not found
     */
    public function getFilesData(string $key): ?array
    {
        return $this->getData($key, RequestData::Upload);
    }

    /**
     * Retrieve raw request data.
     *
     * @deprecated Use getRawData() instead
     *
     * @return string The raw request data as string
     */
    public function get_raw_data(): string
    {
        return $this->getRawData();
    }

    /**
     * Retrieve raw request data.
     *
     * @return string The raw request data as string
     */
    public function getRawData(): string
    {
        return $this->getData(type: RequestData::Raw);
    }

    /**
     * Retrieve request data.
     *
     * @deprecated Use getData() instead
     *
     * @param string|null $key  Key for the value to retrieve
     * @param RequestData $type Type of the request data
     *
     * @return ($type is RequestData::CliOption ? string[] :
     *         ($type is RequestData::CliArgument ? string|null :
     *         ($type is RequestData::Get ? string|string[] :
     *         ($type is RequestData::Post ? string|string[] :
     *         ($type is RequestData::Cookie ? string|string[] :
     *         ($type is RequestData::Server ? float|int|string|string[] :
     *         ($type is RequestData::Header ? string|null :
     *         ($type is RequestData::Raw ? string :
     *         ($type is RequestData::Upload ? UploadData|null : float|int|string|array|null))))))))) Request data value
     */
    public function get_data(?string $key = NULL, RequestData $type = RequestData::Get): mixed
    {
        return $this->getData($key, $type);
    }

    /**
     * Retrieve request data.
     *
     * @param string|null $key  Key for the value to retrieve
     * @param RequestData $type Type of the request data
     *
     * @return ($type is RequestData::CliOption ? string[] :
     *         ($type is RequestData::CliArgument ? string|null :
     *         ($type is RequestData::Get ? string|string[]|null :
     *         ($type is RequestData::Post ? string|string[]|null :
     *         ($type is RequestData::Cookie ? string|string[]|null :
     *         ($type is RequestData::Server ? float|int|string|string[]|null :
     *         ($type is RequestData::Header ? string|null :
     *         ($type is RequestData::Raw ? string :
     *         ($type is RequestData::Upload ? UploadData|null : float|int|string|array|null))))))))) Request data value
     */
    public function getData(?string $key = NULL, RequestData $type = RequestData::Get): float|int|string|array|null
    {
        $property = $type->value;

        switch ($type)
        {
            case RequestData::Get:
            case RequestData::Post:
                if ($key === NULL)
                {
                    if (!array_key_exists($property, $this->mock))
                    {
                        return $this->$property;
                    }

                    return array_merge($this->$property, $this->mock[$property]);
                }

                if (array_key_exists($property, $this->mock) && array_key_exists($key, $this->mock[$property]))
                {
                    return $this->mock[$property][$key];
                }

                return $this->$property[$key] ?? NULL;
            case RequestData::Cookie:
            case RequestData::Upload:
            case RequestData::Server:
                return $this->$property[$key] ?? NULL;
            case RequestData::Header:
                $httpKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                return $this->server[$httpKey] ?? NULL;
            case RequestData::Raw:
                $input = $this->parser->parse_raw_data();

                if ($input !== FALSE)
                {
                    $this->rawData = $input;
                }

                return $this->rawData;
            case RequestData::CliArgument:
                if ($key === NULL)
                {
                    return $this->cliArgs;
                }

                return $this->cliArgs[$key] ?? NULL;
            case RequestData::CliOption:
                return array_keys($this->cliArgs);
            default:
                return NULL;
        }
    }

}

?>
