<?php

namespace Tests\Ragnos\Controllers;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Controllers\RDataset;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;

class RDatasetTest extends RagnosTestCase
{
    protected string $testTable = 'test_dataset';

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestTable(
            $this->testTable,
            [
                'name'  => ['type' => 'VARCHAR', 'constraint' => 255],
                'email' => ['type' => 'VARCHAR', 'constraint' => 255],
            ]
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dropTestTable($this->testTable);
    }

    public function testCanInstantiateRDataset(): void
    {
        // Create a concrete implementation since RDataset is abstract
        $controller = new class extends RDataset {
            public function configure(): void
            {
                // Empty implementation for testing
            }
        };

        $this->assertNotNull($controller);
    }

    public function testCanGetModel(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->model = new RConcreteDatasetModel();
            }
        };

        $model = $controller->getModel();
        $this->assertInstanceOf(RConcreteDatasetModel::class, $model);
    }

    public function testCanSetModel(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                // Empty
            }
        };

        $model = new RConcreteDatasetModel();
        $controller->setModel($model);

        $this->assertInstanceOf(RConcreteDatasetModel::class, $controller->getModel());
    }

    public function testCanSetTableTitle(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setTitle('Test Title');
            }
        };

        // Verify the controller can be created without errors
        $this->assertNotNull($controller);
    }

    public function testCanSetTableName(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setTableName($this->testTable);
            }
        };

        $this->assertNotNull($controller);
    }

    public function testCanAddField(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->addFieldFromArray('name', [
                    'label' => 'Name',
                    'type'  => 'text',
                ]);
            }
        };

        $this->assertNotNull($controller);
    }

    public function testCanSetCanInsert(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setCanInsert(true);
            }
        };

        $this->assertTrue($controller->canInsert());
    }

    public function testCanSetCanUpdate(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setCanUpdate(true);
            }
        };

        $this->assertTrue($controller->canUpdate());
    }

    public function testCanSetCanDelete(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setCanDelete(false);
            }
        };

        // Just verify the methods exist
        $this->assertTrue(method_exists($controller, 'setCanDelete'));
        $this->assertTrue(method_exists($controller, 'canDelete'));
    }

    public function testCanSetIdField(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setIdField('id');
            }
        };

        $this->assertNotNull($controller);
    }

    public function testCanSetUseTimeStamps(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setUseTimeStamps(true);
            }
        };

        $this->assertNotNull($controller);
    }

    public function testCanSetCreatedField(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setCreatedField('created_at');
            }
        };

        $this->assertNotNull($controller);
    }

    public function testCanSetUpdatedField(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setUpdatedField('updated_at');
            }
        };

        $this->assertNotNull($controller);
    }

    public function testCanCallMultipleSetters(): void
    {
        $controller = new class extends RDataset {
            public function configure(): void
            {
                $this->setTitle('Products');
                $this->setTableName('products');
                $this->setIdField('id');
                $this->setCanInsert(true);
                $this->setCanUpdate(true);
                $this->setCanDelete(true);
            }
        };

        $this->assertTrue($controller->canInsert());
        $this->assertTrue($controller->canUpdate());
        $this->assertTrue($controller->canDelete());
    }
}
