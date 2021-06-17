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

use GD75\RequestBodyValidator\RequestBodyValidator;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

/**
 * Class ValidatorTest.
 * Tests the different validator cases.
 */
class ValidatorTest extends TestCase
{
    private RequestBodyValidator $rbv;

    protected function setUp(): void
    {
        $request = (new ServerRequest("POST", "::1"))->withParsedBody(
            [
                "datetime" => "2021-03-21 18:08:23",
                "invalidDatetime" => "20203-21 180823",
                "empty" => "",
                "nonEmpty" => "bytes",
                "numeric0" => "25",
                "numeric1" => "27.5",
                "text" => "lorem",
                "checkboxPresentValue" => true,
                "zero" => "0"
            ]
        );
        $this->rbv = new RequestBodyValidator($request);
    }

    /**
     * Tests the exists validator.
     * @test
     */
    public function existTest()
    {
        $this->assertEquals(true, $this->rbv->validateOne("datetime", RequestBodyValidator::EXISTS));
        $this->assertEquals(false, $this->rbv->validateOne("404-notfound", RequestBodyValidator::EXISTS));
    }

    /**
     * Tests the non empty validator.
     * @test
     */
    public function nonEmptyTest()
    {
        $this->assertEquals(true, $this->rbv->validateOne("nonEmpty", RequestBodyValidator::NOT_EMPTY));
        $this->assertEquals(false, $this->rbv->validateOne("empty", RequestBodyValidator::NOT_EMPTY));
        $this->assertEquals(true, $this->rbv->validateOne("zero", RequestBodyValidator::NOT_EMPTY));
    }

    /**
     * Tests the numeric validator.
     * @test
     */
    public function numericTest()
    {
        $this->assertEquals(true, $this->rbv->validateOne("numeric0", RequestBodyValidator::NUMERIC));
        $this->assertEquals(true, $this->rbv->validateOne("numeric1", RequestBodyValidator::NUMERIC));
        $this->assertEquals(false, $this->rbv->validateOne("text", RequestBodyValidator::NUMERIC));
    }

    /**
     * Tests the not numeric validator.
     * @test
     */
    public function notNumericTest()
    {
        $this->assertEquals(false, $this->rbv->validateOne("numeric0", RequestBodyValidator::NOT_NUMERIC));
        $this->assertEquals(false, $this->rbv->validateOne("numeric1", RequestBodyValidator::NOT_NUMERIC));
        $this->assertEquals(true, $this->rbv->validateOne("text", RequestBodyValidator::NOT_NUMERIC));
    }

    /**
     * Tests the date format validator.
     * @test
     */
    public function dateFormatTest()
    {
        $this->assertEquals(true, $this->rbv->validateOne("datetime", RequestBodyValidator::DATE_FORMAT));
        $this->assertEquals(false, $this->rbv->validateOne("invalidDatetime", RequestBodyValidator::DATE_FORMAT));
    }
}