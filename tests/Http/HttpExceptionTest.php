<?php

declare(strict_types=1);

namespace Frostal\Tests\Http;

use Frostal\Http\HttpException;
use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    public function testCodeMessage()
    {
        $this->expectExceptionMessage("Not Found");
        throw new HttpException(404);
    }

    public function testInvalidCode()
    {
        $this->expectException(\RuntimeException::class);
        throw new HttpException(0);
    }

    public function testErrors()
    {
        $exception = new HttpException(422);
        $exception->setErrorMessages("foo", ["bar"]);
        $exception->addErrorMessage("foo", "baz");
        $this->assertEquals(["foo" => ["bar", "baz"]], $exception->getErrors());
    }
}
