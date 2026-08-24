<?php

namespace Tests\App\Commands;

use App\Commands\MakeDataset;
use App\Commands\MakeQuery;
use CodeIgniter\Test\CIUnitTestCase;

final class MakeCommandsTest extends CIUnitTestCase
{
    public function testMakeDatasetDelegatesToReusableGenerator(): void
    {
        $command = (new \ReflectionClass(MakeDataset::class))->newInstanceWithoutConstructor();
        $source = $command->generateControllerSource(
            'App\\Controllers\\Demo',
            'Tasks',
            'tasks',
            'id_task',
            [['name' => 'title', 'config' => ['label' => 'Title', 'type' => 'text', 'rules' => 'required']]],
            ['title'],
        );

        $this->assertStringContainsString('extends RDatasetController', $source);
        $this->assertStringContainsString("setTableName('tasks')", $source);
        $this->assertStringContainsString("addField('title'", $source);
    }

    public function testMakeQueryDelegatesToReusableGeneratorWithoutBreakingSql(): void
    {
        $command = (new \ReflectionClass(MakeQuery::class))->newInstanceWithoutConstructor();
        $source = $command->generateControllerSource(
            'App\\Controllers\\Reports',
            'TaskReport',
            "SELECT title, '\$value', 'C:\\\\reports' FROM tasks;",
            'id_task',
            [['name' => 'title', 'config' => ['label' => 'Title', 'type' => 'text']]],
            ['title'],
        );

        $this->assertStringContainsString('extends RQueryController', $source);
        $this->assertStringContainsString("setQuery('SELECT title", $source);
        $this->assertStringContainsString("setTableFields(['title'])", $source);
    }
}
