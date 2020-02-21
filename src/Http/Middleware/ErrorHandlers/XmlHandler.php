<?php

declare(strict_types=1);

namespace Frostal\Http\Middleware\ErrorHandlers;

use Frostal\Http\HttpException;
use SimpleXMLElement;
use Traversable;
use Whoops\Exception\Formatter;
use Whoops\Exception\Inspector;
use Whoops\Handler\Handler;

class XmlHandler extends Handler
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

        echo self::toXml($response);

        return Handler::QUIT;
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'application/xml';
    }

    /**
     * @param  SimpleXMLElement  $node Node to append data to, will be modified in place
     * @param  array|\Traversable $data
     * @return SimpleXMLElement  The modified node, for chaining
     */
    private static function addDataToNode(SimpleXMLElement $node, $data)
    {
        assert(is_array($data) || $data instanceof Traversable);

        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                // Convert the key to a valid string
                $key = "unknownNode_" . (string) $key;
            }

            // Delete any char not allowed in XML element names
            $key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

            if (is_array($value)) {
                $child = $node->addChild($key);
                self::addDataToNode($child, $value);
            } else {
                $value = str_replace('&', '&amp;', print_r($value, true));
                $node->addChild($key, $value);
            }
        }

        return $node;
    }

    /**
     * The main function for converting to an XML document.
     *
     * @param  array|\Traversable $data
     * @return string            XML
     */
    private static function toXml($data)
    {
        assert(is_array($data) || $data instanceof Traversable);

        $node = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><root />");

        return self::addDataToNode($node, $data)->asXML();
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
