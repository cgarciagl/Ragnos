<?php

namespace Tests\Ragnos\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

class UtilesHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Load the helper
        helper('App\ThirdParty\Ragnos\Helpers\utiles_helper');
    }

    public function testIsJsonFunctionExists(): void
    {
        $this->assertTrue(function_exists('isJson'));
    }

    public function testRemoveNewLinesFunctionExists(): void
    {
        $this->assertTrue(function_exists('removeNewLines'));
    }

    public function testIsJsonReturnsTrueForValidJson(): void
    {
        $json   = '{"name":"John","age":30}';
        $result = isJson($json);
        $this->assertTrue($result);
    }

    public function testIsJsonReturnsFalseForInvalidJson(): void
    {
        $result = isJson('not json at all');
        $this->assertFalse($result);
    }

    public function testIsJsonReturnsTrueForJsonArray(): void
    {
        $json   = '["item1","item2","item3"]';
        $result = isJson($json);
        $this->assertTrue($result);
    }

    public function testIsJsonReturnsFalseForXml(): void
    {
        $xml    = '<root><item>value</item></root>';
        $result = isJson($xml);
        $this->assertFalse($result);
    }

    public function testRemoveNewLinesRemovesLineBreaks(): void
    {
        $text   = "Line 1\nLine 2\nLine 3";
        $result = removeNewLines($text);

        $this->assertStringNotContainsString("\n", $result);
    }

    public function testRemoveNewLinesPreservesContent(): void
    {
        $text   = "Hello\nWorld";
        $result = removeNewLines($text);

        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('World', $result);
    }

    public function testRemoveNewLinesWithWindowsLineEndings(): void
    {
        $text   = "Line 1\r\nLine 2\r\nLine 3";
        $result = removeNewLines($text);

        $this->assertStringNotContainsString("\r\n", $result);
    }

    public function testRemoveNewLinesWithEmptyString(): void
    {
        $result = removeNewLines('');
        $this->assertEquals('', $result);
    }

    public function testIfSetReturnsValueIfSet(): void
    {
        $value  = 'test';
        $result = ifSet($value, 'default');

        $this->assertEquals('test', $result);
    }

    public function testIfSetReturnsDefaultIfNotSet(): void
    {
        $undefined = null;
        $result    = ifSet($undefined, 'default');

        $this->assertEquals('default', $result);
    }

    public function testIfSetWithNullValue(): void
    {
        $value  = null;
        $result = ifSet($value, 'fallback');

        $this->assertEquals('fallback', $result);
    }

    public function testCurrencyFunctionExists(): void
    {
        $this->assertTrue(function_exists('currency'));
    }

    public function testCurrencyReturnsString(): void
    {
        $result = currency(100);
        $this->assertIsString($result);
    }

    public function testCurrencyWithDecimal(): void
    {
        $result = currency(99.99);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}
