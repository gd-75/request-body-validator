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

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestBodyValidator
 * @package GD75\RequestBodyValidator
 */
final class RequestBodyValidator
{
    const EXISTS = 0;
    const NOT_EMPTY = 1;
    const NUMERIC = 2;
    const NOT_NUMERIC = 3;

    private ServerRequestInterface $request;

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
        }
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
        $parsedBody = $this->request->getParsedBody();

        if ($parsedBody !== null) {
            if ($criteria === self::EXISTS) {
                return isset($parsedBody[$field]);
            } elseif ($criteria === self::NOT_EMPTY) {
                return isset($parsedBody[$field]) && !empty($parsedBody[$field]);
            } elseif ($criteria === self::NUMERIC) {
                return isset($parsedBody[$field]) && !empty($parsedBody[$field]) && is_numeric($parsedBody[$field]);
            } elseif ($criteria === self::NOT_NUMERIC) {
                return isset($parsedBody[$field]) && !empty($parsedBody[$field]) && !is_numeric($parsedBody[$field]);
            } else {
                throw new InvalidArgumentException("Invalid validation '{$criteria}'.");
            }
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
}
