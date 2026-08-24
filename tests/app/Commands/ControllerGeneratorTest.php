<?php

namespace Tests\App\Commands;

use App\ThirdParty\Ragnos\Generators\ControllerGenerator;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

final class ControllerGeneratorTest extends CIUnitTestCase
{
    private ControllerGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ControllerGenerator();
    }

    public function testDatasetFieldMappingCoversCommonTypes(): void
    {
        $boolean = $this->generator->mapDatasetField((object) [
            'name'       => 'enabled',
            'type'       => 'tinyint',
            'max_length' => 1,
        ]);
        $varchar = $this->generator->mapDatasetField((object) [
            'name'       => 'display_name',
            'type'       => 'varchar',
            'max_length' => 80,
        ]);
        $image = $this->generator->mapDatasetField((object) [
            'name' => 'profile_image',
            'type' => 'varchar',
        ]);

        $this->assertSame('checkbox', $boolean['type']);
        $this->assertSame('permit_empty', $boolean['rules']);
        $this->assertStringContainsString('|max_length[80]', $varchar['rules']);
        $this->assertSame('image', $image['type']);
        $this->assertSame('permit_empty', $image['rules']);
    }

    public function testQueryFieldMappingCoversReadOnlyTypes(): void
    {
        $this->assertSame('number', $this->generator->mapQueryField((object) ['name' => 'id', 'type' => 'int'])['type']);
        $this->assertSame('money', $this->generator->mapQueryField((object) ['name' => 'amount', 'type' => 'decimal'])['type']);
        $this->assertSame('date', $this->generator->mapQueryField((object) ['name' => 'created', 'type' => 'date'])['type']);
        $this->assertSame('datetime', $this->generator->mapQueryField((object) ['name' => 'updated', 'type' => 'timestamp'])['type']);
        $this->assertSame('textarea', $this->generator->mapQueryField((object) ['name' => 'notes', 'type' => 'text'])['type']);
    }

    public function testGeneratedDatasetControllerIsValidPhp(): void
    {
        $fields = [
            ['name' => 'title', 'config' => ['label' => 'Title', 'type' => 'text', 'rules' => 'required']],
            ['name' => 'active', 'config' => ['label' => 'Active', 'type' => 'checkbox', 'rules' => 'permit_empty']],
        ];
        $source = $this->generator->renderDatasetController(
            'App\\Controllers\\Demo',
            'Tasks',
            'tasks',
            'id_task',
            $fields,
            ['title', 'active'],
        );

        $this->assertGeneratedPhp($source);
        $this->assertStringContainsString("setTableName('tasks')", $source);
        $this->assertStringContainsString("setTableFields(['title', 'active'])", $source);
    }

    public function testGeneratedQueryEscapesComplexSqlAndIsValidPhp(): void
    {
        $sql = "SELECT name, '\$value', 'C:\\\\reports' FROM tasks -- keep this comment";
        $source = $this->generator->renderQueryController(
            'App\\Controllers\\Reports',
            'TaskReport',
            $sql,
            'id_task',
            [['name' => 'name', 'config' => ['label' => 'Name', 'type' => 'text']]],
            ['name'],
        );

        $this->assertGeneratedPhp($source);
        $this->assertStringContainsString("setQuery('SELECT name", $source);
        $this->assertStringContainsString("setIdField('id_task')", $source);
    }

    public function testMetadataQueryRemovesTrailingSemicolon(): void
    {
        $this->assertSame('SELECT * FROM tasks LIMIT 0', $this->generator->normalizeMetadataQuery(" SELECT * FROM tasks; \n"));
    }

    public function testEmptyMetadataQueryIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->generator->normalizeMetadataQuery(' ; ');
    }

    private function assertGeneratedPhp(string $source): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ragnos-generator-') . '.php';
        file_put_contents($path, $source);

        try {
            $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1';
            $output = shell_exec($command) ?? '';
            $this->assertStringContainsString('No syntax errors detected', $output);
        } finally {
            @unlink($path);
        }
    }
}
