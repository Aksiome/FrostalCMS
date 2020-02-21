<?php

declare(strict_types=1);

namespace Frostal\Http\Middleware\ErrorHandlers;

use Frostal\Http\HttpException;
use Whoops\Exception\Formatter;
use Whoops\Exception\Inspector;
use Whoops\Handler\Handler;

class JsonHandler extends Handler
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @return int
     */
    public function handle()
    {
        if (!$this->debug && !($this->getException() instanceof HttpException)) {
            $this->setException(new HttpException(500));
            $this->setInspector(new Inspector($this->getException()));
        }
        
        $exception = $this->getException();
        $response = $exception instanceof HttpException
            ? [
                "code" => $exception->getCode(),
                "message" => $exception->getMessage(),
                "errors" => $exception->getErrors()
            ]
            : [
                "errors" => [
                    Formatter::formatExceptionAsDataArray(
                        $this->getInspector(),
                        $this->debug
                    ),
                ]
            ];

        echo json_encode($response, defined('JSON_PARTIAL_OUTPUT_ON_ERROR') ? JSON_PARTIAL_OUTPUT_ON_ERROR : 0);
        return Handler::QUIT;
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'application/json';
    }

    /**
     * Enable or disable the debug mode
     *
     * @param boolean $debug
     * @return void
     */
    public function debug(bool $debug)
    {
        $this->debug = $debug;
    }
}
