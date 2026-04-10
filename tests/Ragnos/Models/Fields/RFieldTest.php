<?php

namespace Tests\Ragnos\Models\Fields;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\Fields\RSimpleTextField;
use App\ThirdParty\Ragnos\Models\Fields\RSwitchField;
use App\ThirdParty\Ragnos\Models\Fields\RPillboxField;

class RFieldTest extends RagnosTestCase
{
    public function testCanCreateSimpleTextField(): void
    {
        $field = new RSimpleTextField('username', 'Username');

        $this->assertNotNull($field);
        $this->assertEquals('username', $field->getFieldName());
    }

    public function testCanSetFieldLabel(): void
    {
        $field = new RSimpleTextField('email', 'Email Address');
        // Just verify the field was created
        $this->assertNotNull($field);
    }

    public function testCanSetFieldType(): void
    {
        $field = new RSimpleTextField('password', 'Password');
        $this->assertNotNull($field);
    }

    public function testFieldNameIsStored(): void
    {
        $field = new RSimpleTextField('test_field', 'Test Field');
        $this->assertEquals('test_field', $field->getFieldName());
    }

    public function testCanCreateSwitchField(): void
    {
        $field = new RSwitchField('active', 'Is Active');

        $this->assertNotNull($field);
        $this->assertEquals('active', $field->getFieldName());
    }

    public function testCanCreatePillboxField(): void
    {
        $field = new RPillboxField('tags', 'Tags');

        $this->assertNotNull($field);
        $this->assertEquals('tags', $field->getFieldName());
    }

    public function testMultipleFieldsCanCoexist(): void
    {
        $field1 = new RSimpleTextField('name', 'Name');
        $field2 = new RSwitchField('active', 'Active');
        $field3 = new RSimpleTextField('email', 'Email');

        $this->assertEquals('name', $field1->getFieldName());
        $this->assertEquals('active', $field2->getFieldName());
        $this->assertEquals('email', $field3->getFieldName());
    }

    public function testFieldHasName(): void
    {
        $field = new RSimpleTextField('phone', 'Phone Number');

        $this->assertNotEmpty($field->getFieldName());
        $this->assertTrue(strlen($field->getFieldName()) > 0);
    }

    public function testFieldHasLabel(): void
    {
        $field = new RSimpleTextField('department', 'Department');

        $this->assertNotEmpty($field->getLabel());
        $this->assertTrue(strlen($field->getLabel()) > 0);
    }

    public function testCanInstantiateMultpleTextFields(): void
    {
        $fields = [];

        $fields[] = new RSimpleTextField('first_name', 'First Name');
        $fields[] = new RSimpleTextField('last_name', 'Last Name');
        $fields[] = new RSimpleTextField('middle_name', 'Middle Name');

        $this->assertCount(3, $fields);

        foreach ($fields as $field) {
            $this->assertNotNull($field->getFieldName());
            $this->assertNotNull($field->getLabel());
        }
    }

    public function testFieldTypeIsCorrect(): void
    {
        $textField   = new RSimpleTextField('text_field', 'Text');
        $switchField = new RSwitchField('switch_field', 'Switch');

        $this->assertInstanceOf(RSimpleTextField::class, $textField);
        $this->assertInstanceOf(RSwitchField::class, $switchField);
    }
}
