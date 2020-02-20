<?php

declare(strict_types=1);

namespace Frostal\Validation;

use Frostal\Validation\Exceptions\RuleNotFoundException;
use Frostal\Validation\Exceptions\RuleParsingException;

class Validator
{
    use RulesTrait;

    /**
     * @var string[]
     */
    private static $messages;

    /**
     * @var string[][string]
     */
    private $errors;

    /**
     * @param array $messages
     */
    public function __construct(array $messages = [])
    {
        self::$messages = array_merge(require "messages.php", $messages);
    }

    /**
     * Run the validation
     *
     * @param array $data
     * @param array $rules
     * @return array
     */
    public function validate(array $data, array $rules): array
    {
        $parsedRules = $this->parseRules($rules);
        $validatedData = $this->validateData($data, $parsedRules);
        if ($this->errors) {
            //THROW EXCEPTION WITH MESSAGE BAG
        }
        return $validatedData;
    }

    /**
     * Validate a data array
     *
     * @param array $data
     * @param array $parsedRules
     * @return array
     */
    public function validateData(array $data, array $parsedRules): array
    {
        $validatedData = [];
        foreach ($parsedRules as $field => $rules) {
            if (!is_array($rules)) {
                throw new RuleParsingException();
            }
            foreach ($rules as $rule => $params) {
                if (array_keys($params) !== range(0, count($params) - 1)) {
                    $params = [0 => $params];
                }
                if (!method_exists($this, $method = "validate" . ucfirst($rule))) {
                    throw new RuleNotFoundException();
                }
                array_unshift($params, isset($data[$field]) ? $data[$field] : null);
                call_user_func_array([$this, $method], $params)
                    ? $validatedData[$field] = $data[$field]
                    : $this->errors[$field][] = $this->formatErrorMessage($rule, $field, $params);
            }
        }
        return $validatedData;
    }

    /**
     * Parse the rules array
     *
     * @param array $rules
     * @return array
     */
    public function parseRules(array $rules): array
    {
        $parsedRules = [];
        foreach ($rules as $field => $fieldRules) {
            $parsedRules[$field] = [];
            if (!is_array($fieldRules)) {
                throw new RuleParsingException();
            }
            foreach ($fieldRules as $key => $rule) {
                if (is_int($key)) {
                    $parsedRules[$field][$rule] = [];
                } else {
                    $rule = (array) $rule;
                    if (array_keys($rule) !== range(0, count($rule) - 1)) {
                        $rule = $this->parseRules($rule);
                    }
                    $parsedRules[$field][$key] = $rule;
                }
            }
        }
        return $parsedRules;
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * @param string $rule
     * @param string $field
     * @param array $errors
     * @return string
     */
    public function formatErrorMessage(string $rule, string $field, array $errors = []): string
    {
        return sprintf(self::$messages[$rule], $field, ...$errors);
    }
}
