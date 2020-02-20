<?php

declare(strict_types=1);

namespace Frostal\Validation;

trait RulesTrait
{
    /**
     * Validate a defined and not empty value
     *
     * @param mixed $value
     * @return boolean
     */
    public function validateRequired($value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if (!$value) {
            return false;
        }
        return true;
    }

    /**
     * Validate that two values match
     *
     * @param mixed $value
     * @param mixed $expected
     * @return boolean
     */
    public function validateEquals($value, $expected): bool
    {
        return $value === $expected;
    }

    /**
     * Validate that two values differ
     *
     * @param mixed $value
     * @param mixed $expected
     * @return boolean
     */
    public function validateDifferent($value, $expected): bool
    {
        return $value !== $expected;
    }

    /**
     * Validate a number
     *
     * @param mixed $value
     * @return boolean
     */
    public function validateNumeric($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validate an integer
     *
     * @param mixed $value
     * @return boolean
     */
    public function validateInteger($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate a boolean
     *
     * @param mixed $value
     * @return boolean
     */
    public function validateBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
    }

    /**
     * Validate an array
     *
     * @param mixed $value
     * @param mixed[] params
     * @return boolean
     */
    public function validateArray($value, $params = []): bool
    {
        if ($params) {
            $this->validateData($value, $params);
        }
        return is_array($value);
    }

    /**
     * Validate the length of a string
     *
     * @param mixed $value
     * @param integer $length
     * @return boolean
     */
    public function validateLength($value, int $length): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return strlen(trim($value)) === $length;
    }

    /**
     * Validate the length of a string is between min and max lengths values
     *
     * @param mixed $value
     * @param integer $minLength
     * @param integer $maxLength
     * @return boolean
     */
    public function validateBetweenLength($value, int $minLength, int $maxLength): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $length = strlen(trim($value));
        return $length >= $minLength && $length <= $maxLength;
    }

    /**
     * Validate the length of a string is greater than the min length value
     *
     * @param mixed $value
     * @param integer $minLength
     * @return boolean
     */
    public function validateMinLength($value, int $minLength): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return strlen(trim($value)) >= $minLength;
    }

    /**
     * Validate the length of a string is less than the max length value
     *
     * @param mixed $value
     * @param integer $maxLength
     * @return boolean
     */
    public function validateMaxLength($value, int $maxLength): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return strlen(trim($value)) <= $maxLength;
    }

    /**
     * Validate the size of a number or an array
     *
     * @param mixed $value
     * @param integer $size
     * @return boolean
     */
    public function validateSize($value, int $size): bool
    {
        return $this->getValueSize($value) === $size;
    }

    /**
     * Validate the size of a number or an array is between min and max values
     *
     * @param mixed $value
     * @param integer $min
     * @param integer $max
     * @return boolean
     */
    public function validateBetween($value, int $min, int $max): bool
    {
        $size = $this->getValueSize($value);
        if (is_null($size)) {
            return false;
        }
        return $size >= $min && $size <= $max;
    }

    /**
     * Validate the size of a number or an array is greater than the min value
     *
     * @param mixed $value
     * @param integer $min
     * @return boolean
     */
    public function validateMin($value, int $min): bool
    {
        $size = $this->getValueSize($value);
        if (is_null($size)) {
            return false;
        }
        return $size >= $min;
    }

    /**
     * Validate the size of a number or an array is less than the max value
     *
     * @param mixed $value
     * @param integer $max
     * @return boolean
     */
    public function validateMax($value, int $max): bool
    {
        $size = $this->getValueSize($value);
        if (is_null($size)) {
            return false;
        }
        return $size <= $max;
    }

    /**
     * Validate that params exists in the value array
     *
     * @param mixed $value
     * @param mixed ...$params
     * @return boolean
     */
    public function validateInArray($value, ...$params): bool
    {
        if (!is_array($value)) {
            return false;
        }
        foreach ($params as $needle) {
            if (!in_array($needle, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate that params does not exist in the value array
     *
     * @param mixed $value
     * @param mixed ...$params
     * @return boolean
     */
    public function validateNotInArray($value, ...$params): bool
    {
        if (!is_array($value)) {
            return false;
        }
        foreach ($params as $needle) {
            if (in_array($needle, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate an email
     *
     * @param mixed $value
     * @return boolean
     */
    public function validateEmail($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate an url
     *
     * @param mixed $value
     * @return boolean
     */
    public function validateUrl($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate a regex
     *
     * @param mixed $value
     * @param string $pattern
     * @return boolean
     */
    public function validateRegex($value, string $pattern): bool
    {
        return filter_var($value, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => $pattern]]) !== false;
    }

    /**
     * Validate a date
     *
     * @param mixed $value
     * @return boolean
     */
    public function validateDate($value): bool
    {
        return ($value instanceof \DateTime) ? true : strtotime($value) !== false;
    }

    /**
     * Validate the date is before a given date
     *
     * @param mixed $value
     * @param DateTime|string $time
     * @return boolean
     */
    public function validateDateBefore($value, $date): bool
    {
        $time = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
        $expectedTime = ($date instanceof \DateTime) ? $date->getTimestamp() : strtotime($date);
        return $time < $expectedTime;
    }

    /**
     * Validate the date is after a given date
     *
     * @param mixed $value
     * @param DateTime|string $time
     * @return boolean
     */
    public function validateDateAfter($value, $date): bool
    {
        $time = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
        $expectedTime = ($date instanceof \DateTime) ? $date->getTimestamp() : strtotime($date);
        return $time > $expectedTime;
    }

    /**
     * @param mixed $value
     * @return integer|null
     */
    private function getValueSize($value): ?int
    {
        if (is_numeric($value)) {
            return (int) $value;
        } elseif (is_array($value)) {
            return count($value);
        }
        return null;
    }
}
