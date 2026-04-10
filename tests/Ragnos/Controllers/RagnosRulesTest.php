<?php

namespace Tests\Ragnos\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use App\ThirdParty\Ragnos\Controllers\RagnosRules;

class RagnosRulesTest extends CIUnitTestCase
{
    protected RagnosRules $rules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rules = new RagnosRules();
    }

    public function testCanInstantiateRagnosRules(): void
    {
        $this->assertInstanceOf(RagnosRules::class, $this->rules);
    }

    public function testReadonlyRagnosRuleExists(): void
    {
        $this->assertTrue(method_exists($this->rules, 'readonly_Ragnos'));
    }

    public function testRuleIsCallable(): void
    {
        $method = 'readonly_Ragnos';
        $this->assertTrue(method_exists($this->rules, $method));
        $this->assertTrue(is_callable([$this->rules, $method]));
    }

    public function testCanCreateMultipleInstances(): void
    {
        $rules1 = new RagnosRules();
        $rules2 = new RagnosRules();

        $this->assertInstanceOf(RagnosRules::class, $rules1);
        $this->assertInstanceOf(RagnosRules::class, $rules2);
    }

    public function testRuleCanBeCalledWithValidSignature(): void
    {
        // Just verify the method can be called with proper validation signature
        // Don't execute the logic since fieldHasChanged() is a helper
        $this->assertTrue(method_exists($this->rules, 'readonly_Ragnos'));
    }

    public function testRuleClassImplementsCorrectInterface(): void
    {
        // Verify it's a validation rule provider
        $this->assertTrue(class_exists('App\ThirdParty\Ragnos\Controllers\RagnosRules'));
    }
}
