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

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

/**
 * Class InitialisationTest.
 * Tests initialisation cases.
 */
class InitialisationTest extends TestCase
{
    private ServerRequest $request;

    protected function setUp(): void
    {
        $this->request = new ServerRequest("POST", "::1", [], null);
    }

    /**
     * Tests the initialisation with an array parsed body.
     * @test
     */
    public function standardInitialisationTest()
    {
        $this->expectNotToPerformAssertions();

        $this->request = $this->request->withParsedBody(["field" => "value"]);
        new GD75\RequestBodyValidator\RequestBodyValidator($this->request);
    }

    /**
     * Tests the initialisation with a null parsed body.
     * @test
     */
    public function nullInitialisationTest()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Only supports parsed body in the forms of array.");

        new GD75\RequestBodyValidator\RequestBodyValidator($this->request);
    }

    /**
     * Tests the initialisation with a non-array/non-null object as parsed body.
     * @test
     */
    public function nonArrayInitialisationTest()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Only supports parsed body in the forms of array.");

        $this->request = $this->request->withParsedBody($this->request);
        new GD75\RequestBodyValidator\RequestBodyValidator($this->request);
    }
}