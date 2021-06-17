<?php
/**
 * MIT License
 * Copyright (c) 2021 Noah Boegli
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace GD75\RequestBodyValidator;

use DateTime;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Class RequestBodyValidator.
 * @package GD75\RequestBodyValidator
 */
final class RequestBodyValidator
{
    const EXISTS = 0;
    const NOT_EMPTY = 1;
    const NUMERIC = 2;
    const NOT_NUMERIC = 3;
    const DATE_FORMAT = 4;

    private ServerRequestInterface $request;
    private array $parsedBody;
    private array $errors;

    /**
     * RequestBodyValidator constructor.
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;

        $parsedBody = $request->getParsedBody();

        if ($parsedBody === null || !is_array($parsedBody)) {
            throw new InvalidArgumentException("Only supports parsed body in the forms of array.");
        }else{
            $this->parsedBody = $parsedBody;
        }

        $this->errors = [];
    }

    /**
     * Retrieve all the fields that were tested and did not match the validation criteria, useful to log or even display
     * them to the user in a nice and likeable way.
     * @return array The fields that did not match their criteria.
     */
    public function getFieldsWithErrors() : array
    {
        return array_keys($this->errors);
    }

    /**
     * Validates multiple fields with one criteria.
     * @param int[]|string[]|boolean[] $fields The name of the fields to validate.
     * @param int $criteria The criteria to validate with (see class constants).
     * @return bool Whether the criteria was validated on every field.
     */
    public function validateMultiple(array $fields, int $criteria): bool
    {
        foreach ($fields as $field) {
            if (!$this->validateOne($field, $criteria)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates a field with one criteria.
     * @param mixed $field The name of the field to validate.
     * @param int $criteria The criteria to validate with (see class constants).
     * @return bool Whether the criteria was validated.
     */
    public function validateOne($field, int $criteria): bool
    {
        if ($this->parsedBody !== null) {
            if ($criteria === self::EXISTS) {
                $status =
                    isset($this->parsedBody[$field]);
            } elseif ($criteria === self::NOT_EMPTY) {
                $status =
                    isset($this->parsedBody[$field])
                    && !empty($this->parsedBody[$field]);
            } elseif ($criteria === self::NUMERIC) {
                $status =
                    isset($this->parsedBody[$field])
                    && !empty($this->parsedBody[$field])
                    && is_numeric($this->parsedBody[$field]);
            } elseif ($criteria === self::NOT_NUMERIC) {
                $status =
                    isset($this->parsedBody[$field])
                    && !empty($this->parsedBody[$field])
                    && !is_numeric($this->parsedBody[$field]);
            } elseif ($criteria === self::DATE_FORMAT) {
                $status =
                    isset($this->parsedBody[$field])
                    && !empty($this->parsedBody[$field])
                    && strtotime($this->parsedBody[$field]) !== false;
            } else {
                throw new InvalidArgumentException("Invalid validation '$criteria'.");
            }

            if(!$status){
                $this->errors[$field] = $criteria;
            }
            return $status;
        } else {
            return false;
        }
    }

    /**
     * Retrieves the value of a single checkbox (ie `name=foo`).
     * @param string $field The name of the field in the parsed body.
     * @return bool Whether the checkbox was checked.
     */
    public function getSingleCheckboxVal(string $field): bool
    {
        return $this->validateOne($field, self::EXISTS);
    }

    /**
     * Retrieves a DateTime object created from the a field of the request.
     * To avoid any unexpected runtime exceptions, you should validate the field with the `DATE_FORMAT` criteria before.
     * @param string $field The field to construct the datetime from.
     * @param bool $throw If the field is non-existent, whether to throw or simply return null, defaults to true.
     * @return \DateTime The DateTime instance.
     * @throws \RuntimeException If the format was not instantiable into a DateTime.
     */
    public function getDateTime(string $field, bool $throw = true): ?DateTime
    {
        try {
            return new DateTime($this->parsedBody[$field]);
        } catch (Exception $e) {
            if($throw){
                throw new RuntimeException("{$this->parsedBody[$field]} is not a valid date format.", $e->getCode(), $e);
            }else{
                return null;
            }
        }
    }

    /**
     * Retrieves a numeric value as raw (input) value (either numeric string, int or float).
     * @param string $field The field to retrieve.
     * @param bool $throw If the field is non-existent, whether to throw or simply return null, defaults to true.
     * @return float|int The field value.
     * @throws \RuntimeException If the field does not exist or is not numeric.
     */
    public function getNumeric(string $field, bool $throw = true)
    {
        if ($this->validateOne($field, self::NUMERIC)) {
            return $this->parsedBody[$field];
        } else {
            if($throw){
                throw new RuntimeException("$field does not exist or is not numeric.");
            }else{
                return null;
            }
        }
    }

    /**
     * Retrieves a numeric value as integer.
     * @param string $field The field to retrieve.
     * @param bool $throw If the field is non-existent, whether to throw or simply return null, defaults to true.
     * @return int The field value.
     * @throws \RuntimeException If the field does not exist or is not numeric.
     */
    public function getInt(string $field, bool $throw = true): ?int
    {
        if ($this->validateOne($field, self::NUMERIC)) {
            return (int) $this->parsedBody[$field];
        } else {
            if($throw){
                throw new RuntimeException("$field does not exist or is not numeric.");
            }else{
                return null;
            }
        }
    }

    /**
     * Retrieves a numeric value as float.
     * @param string $field The field to retrieve.
     * @param bool $throw If the field is non-existent, whether to throw or simply return null, defaults to true.
     * @return float The field value.
     * @throws \RuntimeException If the field does not exist or is not numeric.
     */
    public function getFloat(string $field, bool $throw = true): ?float
    {
        if ($this->validateOne($field, self::NUMERIC)) {
            return (float) $this->parsedBody[$field];
        } else {
            if($throw){
                throw new RuntimeException("$field does not exist or is not numeric.");
            }else{
                return null;
            }
        }
    }

    /**
     * Retrieves a field as string.
     * @param string $field The field to retrieve.
     * @param bool $throw If the field is non-existent, whether to throw or simply return null, defaults to true.
     * @return string The field value.
     * @throws \RuntimeException If the field does not exist.
     */
    public function getString(string $field, bool $throw = true): ?string
    {
        if ($this->validateOne($field, self::EXISTS)) {
            return (string)$this->parsedBody[$field];
        } else {
            if($throw){
                throw new RuntimeException("$field does not exist.");
            }else{
                return null;
            }
        }
    }

    /**
     * Retrieves a field as non empty string.
     * @param string $field The field to retrieve.
     * @param bool $throw If the field is non-existent or empty, whether to throw or simply return null, defaults to true.
     * @return string The field value.
     * @throws \RuntimeException If the field does not exist.
     */
    public function getStringNotEmpty(string $field, bool $throw = true): ?string
    {
        if ($this->validateOne($field, self::NOT_EMPTY)) {
            return (string)$this->parsedBody[$field];
        } else {
            if ($throw) {
                throw new RuntimeException("$field is empty or does not exist.");
            } else {
                return null;
            }
        }
    }
}
