<?php

declare(strict_types=1);

namespace Frostal\Http;

use Exception;
use RuntimeException;
use Throwable;

class HttpException extends Exception
{
    /**
     * @var string[][string]
     */
    protected $errors;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string[]
     */
    protected static $phrases = [
        /* CLIENT ERRORS */
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        /* SERVER ERRORS */
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * Create a new HTTP exception
     *
     * @param integer $code
     */
    public function __construct(int $code = 500, ?Throwable $previous = null)
    {
        if (!isset(self::$phrases[$code])) {
            throw new RuntimeException("Http error code not valid ({$code})");
        }
        parent::__construct(self::$phrases[$code], $code, $previous);
    }

    /**
     * Append a new message to the error
     *
     * @param string $name
     * @param string $message
     * @return self
     */
    public function addErrorMessage(string $name, string $message): self
    {
        $this->errors[$name][] = $message;
        return $this;
    }

    /**
     * Set the messages of an error
     *
     * @param string $name
     * @param array $errors
     * @return self
     */
    public function setErrorMessages(string $name, array $errors): self
    {
        $this->errors[$name] = $errors;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }
}
