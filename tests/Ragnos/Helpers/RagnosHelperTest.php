<?php

namespace Tests\Ragnos\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

class RagnosHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Load the helper
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
    }

    public function testStartsWithFunctionExists(): void
    {
        $this->assertTrue(function_exists('startsWith'));
    }

    public function testEndsWithFunctionExists(): void
    {
        $this->assertTrue(function_exists('endsWith'));
    }

    public function testMoneyFormatFunctionExists(): void
    {
        $this->assertTrue(function_exists('moneyFormat'));
    }

    public function testMoneyToNumberFunctionExists(): void
    {
        $this->assertTrue(function_exists('moneyToNumber'));
    }

    public function testStartsWithReturnsTrueForMatch(): void
    {
        $result = startsWith('Hello World', 'Hello');
        $this->assertTrue($result);
    }

    public function testStartsWithReturnsFalseForNoMatch(): void
    {
        $result = startsWith('Hello World', 'World');
        $this->assertFalse($result);
    }

    public function testEndsWithReturnsTrueForMatch(): void
    {
        $result = endsWith('Hello World', 'World');
        $this->assertTrue($result);
    }

    public function testEndsWithReturnsFalseForNoMatch(): void
    {
        $result = endsWith('Hello World', 'Hello');
        $this->assertFalse($result);
    }

    public function testMoneyFormatReturnsString(): void
    {
        $result = moneyFormat(100.50);
        $this->assertIsString($result);
    }

    public function testMoneyFormatWithZero(): void
    {
        $result = moneyFormat(0);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testMoneyFormatWithLargeNumber(): void
    {
        $result = moneyFormat(9999999.99);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testMoneyToNumberReturnsFloat(): void
    {
        $result = moneyToNumber('100.50');
        $this->assertIsFloat($result);
    }

    public function testMoneyToNumberWithComma(): void
    {
        $result = moneyToNumber('1,000.50');
        $this->assertIsFloat($result);
        $this->assertEquals(1000.50, $result);
    }

    public function testMoneyToNumberWithoutDecimals(): void
    {
        $result = moneyToNumber('100');
        $this->assertIsNumeric($result);
    }
}
