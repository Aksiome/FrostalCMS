<?php

declare(strict_types=1);

namespace Frostal\Tests\Validation;

use Frostal\Validation\Exceptions\RuleNotFoundException;
use Frostal\Validation\Exceptions\RuleParsingException;
use Frostal\Validation\Validator;
use PHPUnit\Framework\TestCase;

class RulesTest extends TestCase
{
    public function testValidationRuleNotFoundException()
    {
        $this->expectException(RuleNotFoundException::class);
        $v = new Validator();
        $v->validateData(["foo" => "bar"], ["foo" => ["impossible" => []]]);
    }

    public function testValidationRuleParsingException()
    {
        $this->expectException(RuleParsingException::class);
        $v = new Validator();
        $v->validateData(["foo" => "bar"], ["impossible"]);
    }

    public function testValidationRuleRequired()
    {
        $v = new Validator();
        $this->assertTrue($v->validateRequired("foo"));
        $this->assertTrue($v->validateRequired(["foo" => "bar"]));
        $this->assertFalse($v->validateRequired(" "));
        $this->assertFalse($v->validateRequired([]));
        $this->assertFalse($v->validateRequired(null));
    }

    public function testValidationRuleEquals(): void
    {
        $v = new Validator();
        $this->assertFalse($v->validateEquals("foo", "bar"));
        $this->assertTrue($v->validateEquals("foo", "foo"));
    }

    public function testValidationRuleDifferent(): void
    {
        $v = new Validator();
        $this->assertTrue($v->validateDifferent("foo", "bar"));
        $this->assertFalse($v->validateDifferent("foo", "foo"));
    }

    public function testValidationRuleNumeric()
    {
        $v = new Validator();
        $this->assertTrue($v->validateNumeric("42"));
        $this->assertTrue($v->validateNumeric("0.01"));
        $this->assertTrue($v->validateNumeric("-1"));
        $this->assertFalse($v->validateNumeric("bar"));
    }

    public function testValidationRuleInteger()
    {
        $v = new Validator();
        $this->assertTrue($v->validateInteger("42"));
        $this->assertFalse($v->validateInteger("0.01"));
        $this->assertTrue($v->validateInteger("-1"));
        $this->assertFalse($v->validateInteger("bar"));
        $this->assertFalse($v->validateInteger([]));
    }

    public function testValidationRuleBoolean()
    {
        $v = new Validator();
        $this->assertTrue($v->validateBoolean("true"));
        $this->assertTrue($v->validateBoolean("false"));
        $this->assertTrue($v->validateBoolean("1"));
        $this->assertTrue($v->validateBoolean("0"));
        $this->assertTrue($v->validateBoolean("yes"));
        $this->assertTrue($v->validateBoolean("no"));
        $this->assertFalse($v->validateBoolean("foo"));
        $this->assertFalse($v->validateBoolean("-1"));
        $this->assertFalse($v->validateBoolean("42"));
        $this->assertFalse($v->validateBoolean([]));
    }

    public function testValidationRuleArray()
    {
        $v = new Validator();
        $this->assertTrue($v->validateArray(["foo" => "bar"]));
        $this->assertTrue($v->validateArray([]));
        $this->assertFalse($v->validateArray("foo"));
    }

    public function testValidationRuleLength()
    {
        $v = new Validator();
        $this->assertTrue($v->validateLength("foo", 3));
        $this->assertTrue($v->validateLength(" foo ", 3));
        $this->assertFalse($v->validateLength([], 0));
    }

    public function testValidationRuleBetweenLength()
    {
        $v = new Validator();
        $this->assertTrue($v->validateBetweenLength("foobar", 3, 10));
        $this->assertFalse($v->validateBetweenLength("foo", 1, 2));
        $this->assertFalse($v->validateBetweenLength([], 0, 0));
    }

    public function testValidationRuleMinLength()
    {
        $v = new Validator();
        $this->assertTrue($v->validateMinLength("foobar", 3));
        $this->assertFalse($v->validateMinLength("foo", 4));
        $this->assertFalse($v->validateMinLength([], 0));
    }

    public function testValidationRuleMaxLength()
    {
        $v = new Validator();
        $this->assertTrue($v->validateMaxLength("foobar", 6));
        $this->assertFalse($v->validateMaxLength("foo", 2));
        $this->assertFalse($v->validateMaxLength([], 0));
    }

    public function testValidationRuleSize()
    {
        $v = new Validator();
        $this->assertTrue($v->validateSize("5", 5));
        $this->assertFalse($v->validateSize("5", 2));
        $this->assertTrue($v->validateSize(["foo" => "bar"], 1));
        $this->assertFalse($v->validateSize(["foo" => "bar"], 2));
    }

    public function testValidationRuleBetween()
    {
        $v = new Validator();
        $this->assertTrue($v->validateBetween("5", 5, 10));
        $this->assertFalse($v->validateBetween("1", 2, 10));
        $this->assertTrue($v->validateBetween(["foo" => "bar", "baz" => "qux"], 1, 2));
        $this->assertFalse($v->validateBetween(["foo" => "bar"], 2, 3));
        $this->assertFalse($v->validateBetween("", 0, 0));
    }

    public function testValidationRuleMin()
    {
        $v = new Validator();
        $this->assertTrue($v->validateMin("5", 3));
        $this->assertFalse($v->validateMin("5", 6));
        $this->assertTrue($v->validateMin(["foo" => "bar"], 1));
        $this->assertFalse($v->validateMin(["foo" => "bar"], 2));
        $this->assertFalse($v->validateMin("", 0, 0));
    }

    public function testValidationRuleMax()
    {
        $v = new Validator();
        $this->assertTrue($v->validateMax("5", 7));
        $this->assertFalse($v->validateMax("5", 3));
        $this->assertTrue($v->validateMax(["foo" => "bar"], 2));
        $this->assertFalse($v->validateMax(["foo" => "bar", "baz" => "qux"], 1));
        $this->assertFalse($v->validateMax("", 0, 0));
    }

    public function testValidationRuleInArray()
    {
        $v = new Validator();
        $this->assertTrue($v->validateInArray(["foo" => "bar", "baz" => "qux"], "bar", "qux"));
        $this->assertTrue($v->validateInArray(["foo" => ["bar" => "baz"]], ["bar" => "baz"]));
        $this->assertFalse($v->validateInArray(["foo" => "bar"], "baz"));
        $this->assertFalse($v->validateInArray("foo"));
    }

    public function testValidationRuleNotInArray()
    {
        $v = new Validator();
        $this->assertFalse($v->validateNotInArray(["foo" => "bar", "baz" => "qux"], "bar", "qux"));
        $this->assertFalse($v->validateNotInArray(["foo" => ["bar" => "baz"]], ["bar" => "baz"]));
        $this->assertTrue($v->validateNotInArray(["foo" => "bar"], "baz"));
        $this->assertFalse($v->validateNotInArray("foo"));
    }

    public function testValidationRuleEmail()
    {
        $v = new Validator();
        $this->assertTrue($v->validateEmail("foo@bar.baz"));
        $this->assertFalse($v->validateEmail("foo"));
    }

    public function testValidationRuleUrl()
    {
        $v = new Validator();
        $this->assertTrue($v->validateUrl("https://foo"));
        $this->assertTrue($v->validateUrl("http://foo"));
        $this->assertFalse($v->validateUrl("foo"));
    }
    
    public function testValidationRuleRegex()
    {
        $v = new Validator();
        $this->assertTrue($v->validateRegex("url-regex", "/^[a-z0-9-]+$/"));
        $this->assertFalse($v->validateRegex("#url-regex", "/^[a-z0-9-]+$/"));
    }

    public function testValidationRuleDate()
    {
        $v = new Validator();
        $this->assertTrue($v->validateDate(new \DateTime()));
        $this->assertTrue($v->validateDate("01-02-2019"));
        $this->assertFalse($v->validateDate("foo"));
    }

    public function testValidationRuleDateBefore()
    {
        $v = new Validator();
        $this->assertTrue($v->validateDateBefore(
            new \DateTime(),
            (new \DateTime())->add(new \DateInterval("P1Y"))
        ));
        $this->assertTrue($v->validateDateBefore("01-02-2019", "02-03-2019"));
        $this->assertFalse($v->validateDateBefore("02-03-2019", "01-02-2019"));
    }

    public function testValidationRuleDateAfter()
    {
        $v = new Validator();
        $this->assertFalse($v->validateDateAfter(
            new \DateTime(),
            (new \DateTime())->add(new \DateInterval("P1Y"))
        ));
        $this->assertFalse($v->validateDateAfter("01-02-2019", "02-03-2019"));
        $this->assertTrue($v->validateDateAfter("02-03-2019", "01-02-2019"));
    }
}
