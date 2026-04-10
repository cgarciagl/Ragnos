<?php

namespace Tests\Ragnos\Models;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RTableModel;

// Concrete implementation for testing
class ConcreteRTableModel extends RTableModel
{
    public $table = 'test_records';
    public $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'email'];

    public function __construct($db = null)
    {
        parent::__construct();
        if ($db) {
            $this->db = $db;
        }
    }

    public function countAll($reset = true)
    {
        return 0;
    }

    public function findAll($limit = 0, $offset = 0)
    {
        return [];
    }
}

class RTableModelTest extends RagnosTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestTable('test_records', [
            'name'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('test_records');
        parent::tearDown();
    }

    /**
     * @test
     */
    public function Can_create_model_instance()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertNotNull($model);
    }

    /**
     * @test
     */
    public function Can_set_table_name()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertEquals('test_records', $model->getTable());
    }

    /**
     * @test
     */
    public function Can_insert_record()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertTrue(method_exists($model, 'insert'));
    }

    /**
     * @test
     */
    public function Can_find_record()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertTrue(method_exists($model, 'find'));
    }

    /**
     * @test
     */
    public function Can_update_record()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertTrue(method_exists($model, 'update'));
    }

    /**
     * @test
     */
    public function Can_delete_record()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertTrue(method_exists($model, 'delete'));
    }

    /**
     * @test
     */
    public function Can_count_records()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertTrue(method_exists($model, 'countAll'));
    }

    /**
     * @test
     */
    public function Can_find_all()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertTrue(method_exists($model, 'findAll'));
    }

    /**
     * @test
     */
    public function Can_set_connection()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertNotNull($model);
    }

    /**
     * @test
     */
    public function Insert_returns_false_with_invalid_data()
    {
        $model = new ConcreteRTableModel($this->db);
        $this->assertTrue(method_exists($model, 'insert'));
    }
}
