<?php

declare(strict_types=1);

namespace Frostal\Tests\Validation;

use Frostal\Validation\Exceptions\RuleParsingException;
use Frostal\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidationRulesParsing()
    {
        $v = new Validator();
        $rules = $v->parseRules([
            "foo" => ["required", "minLength" => 3],
            "bar" => ["required", "array" => [
                "foo" => ["required", "betweenLength" => [100, 500]],
                "bar" => ["inArray" => "Hello World"]
            ]]
        ]);
        $this->assertEquals([
            "foo" => ["required" => [], "minLength" => [3]],
            "bar" => [
                "required" => [],
                "array" => [
                    "foo" => ["required" => [], "betweenLength" => [100, 500]],
                    "bar" => ["inArray" => ["Hello World"]]
                ]
            ]
        ], $rules);
    }

    public function testValidationRulesParsingException()
    {
        $this->expectException(RuleParsingException::class);
        $v = new Validator();
        $v->parseRules([
            "foo" => ["impossible" => [
                "foo" => "Hello",
                "bar" => "World"
            ]]
        ]);
    }

    public function testValidation()
    {
        $v = new Validator();
        $v->validate(["foo" => "bar"], ["foo" => ["required"]]);
        $this->assertNull($v->getErrors());
    }

    public function testDataValidation()
    {
        $data = ["foo" => "bar"];
        $v = new Validator();
        $vData = $v->validateData($data, ["foo" => ["required" => []]]);
        $this->assertEquals($data, $vData);
        $this->assertNull($v->getErrors());
    }

    public function testInvalidDataValidation()
    {
        $data = ["foo" => "bar"];
        $v = new Validator();
        $v->validateData($data, ["baz" => ["required" => []]]);
        $this->assertNotNull($v->getErrors());
    }

    public function testNestedDataValidation()
    {
        $data = ["foo" => [
            "bar" => "Hello",
            "baz" => "World"
        ]];
        $v = new Validator();
        $vData = $v->validateData($data, ["foo" => [
            "required" => [],
            "array" => [
                "bar" => ["required" => []],
                "baz" => ["required" => []]
            ]
        ]]);
        $this->assertEquals($data, $vData);
        $this->assertNull($v->getErrors());
    }

    public function testValidationArguments()
    {
        $data = ["foo" => "bar"];
        $v = new Validator();
        $vData = $v->validateData($data, ["foo" => ["betweenLength" => [2, 5]]]);
        $this->assertEquals($data, $vData);
        $this->assertNull($v->getErrors());
    }

    public function testValidationMessages()
    {
        $v = new Validator(["foo" => "Hello %s the %s"]);
        $msg = $v->formatErrorMessage("foo", "to", ["World"]);
        $this->assertEquals("Hello to the World", $msg);
    }
}
