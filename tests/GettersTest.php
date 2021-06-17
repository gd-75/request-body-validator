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
 * Class GettersTest.
 * Tests the different validator cases.
 */
class GettersTest extends TestCase
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
                "checkboxPresent" => true,
                "floating" => 1.22,
                "integer" => 36
            ]
        );
        $this->rbv = new RequestBodyValidator($request);
    }


    /**
     * Tests the erroneous field retrieval.
     * @test
     */
    public function errorsRetrievalTest()
    {
        $this->rbv->validateMultiple(["datetime", "invalidDatetime"], RequestBodyValidator::DATE_FORMAT);
        $this->assertEquals(["invalidDatetime"], $this->rbv->getFieldsWithErrors());
    }

    /**
     * Tests the datetime getter.
     * @test
     */
    public function getDateTimeTest()
    {
        $datetime = new DateTime("2021-03-21 18:08:23");

        $this->assertEquals($datetime, $this->rbv->getDateTime("datetime"));
        $this->assertEquals(null, $this->rbv->getDateTime("invalidDatetime", false));

        $this->expectExceptionMessage("20203-21 180823 is not a valid date format");
        $this->rbv->getDateTime("invalidDatetime");
    }

    /**
     * Tests the checkbox getter.
     * @test
     */
    public function getCheckboxValue()
    {
        $this->assertEquals(true, $this->rbv->getSingleCheckboxVal("checkboxPresent"));
        $this->assertEquals(false, $this->rbv->getSingleCheckboxVal("waitItDoesNotExist"));
    }

    /**
     * Tests the numeric getter.
     * @test
     */
    public function getNumeric()
    {
        $this->assertEquals("25", $this->rbv->getNumeric("numeric0"));
        $this->assertEquals("27.5", $this->rbv->getNumeric("numeric1"));
        $this->assertEquals(1.22, $this->rbv->getNumeric("floating"));
        $this->assertEquals(36, $this->rbv->getNumeric("integer"));
        $this->assertEquals(null, $this->rbv->getNumeric("edfiwgjewig", false));
    }

    /**
     * Tests the int getter.
     * @test
     */
    public function getInt()
    {
        $this->assertEquals(25, $this->rbv->getInt("numeric0"));
        $this->assertEquals(27, $this->rbv->getInt("numeric1"));
        $this->assertEquals(1, $this->rbv->getInt("floating"));
        $this->assertEquals(36, $this->rbv->getInt("integer"));
        $this->assertEquals(null, $this->rbv->getInt("32zu542", false));
    }

    /**
     * Tests the float getter.
     * @test
     */
    public function getFloat()
    {
        $this->assertEquals(25.0, $this->rbv->getFloat("numeric0"));
        $this->assertEquals(27.5, $this->rbv->getFloat("numeric1"));
        $this->assertEquals(1.22, $this->rbv->getFloat("floating"));
        $this->assertEquals(36.0, $this->rbv->getFloat("integer"));
        $this->assertEquals(null, $this->rbv->getFloat("apwqfqow", false));
    }

    /**
     * Tests the string getter.
     * @test
     */
    public function getString()
    {
        $this->assertSame("lorem", $this->rbv->getString("text"));
        $this->assertSame("27.5", $this->rbv->getString("numeric1"));
        $this->assertSame("1.22", $this->rbv->getString("floating"));
        $this->assertSame("", $this->rbv->getString("empty"));
        $this->assertSame(null, $this->rbv->getString("qdqf", false));
    }

    /**
     * Tests the string not empty getter.
     * @test
     */
    public function getStringNotEmpty()
    {
        $this->assertSame("lorem", $this->rbv->getStringNotEmpty("text"));
        $this->assertSame("27.5", $this->rbv->getStringNotEmpty("numeric1"));
        $this->assertSame("1.22", $this->rbv->getStringNotEmpty("floating"));
        $this->assertSame(null, $this->rbv->getStringNotEmpty("empty", false));
    }
}