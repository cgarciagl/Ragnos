<?php

namespace Tests\Ragnos;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Database\Connection;
use CodeIgniter\Config\Services;

/**
 * Base test case for Ragnos module tests
 * Provides common setup, database, and helper methods
 */
class RagnosTestCase extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize in-memory SQLite database
        $this->db = $this->initializeDatabase();

        // Set default locale
        Services::request()->setLocale('es');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Close database connection
        if ($this->db) {
            $this->db->close();
        }
    }

    /**
     * Initialize in-memory SQLite database for testing
     */
    protected function initializeDatabase()
    {
        $config = config('Database');

        // Use SQLite in-memory for tests
        $config->tests['DBDriver'] = 'SQLite3';
        $config->tests['database'] = ':memory:';
        $config->tests['DBDebug']  = false;

        return \Config\Database::connect('tests');
    }

    /**
     * Create a test table with common schema
     */
    protected function createTestTable(
        string $tableName,
        array $columns = [],
        bool $timestamps = false,
        bool $softDeletes = false
    ): void {
        // Check if table already exists
        if ($this->tableExists($tableName)) {
            $this->db->query("DROP TABLE {$tableName}");
        }

        // Default columns for SQLite
        $defaultColumns = [
            'id INTEGER PRIMARY KEY AUTOINCREMENT',
        ];

        // Build column definitions
        $columnDefs = $defaultColumns;

        // Add custom columns
        foreach ($columns as $name => $config) {
            $colDef = "{$name} {$config['type']}";

            // Add constraints (without size for strings in SQLite)
            if (($config['null'] ?? false)) {
                $colDef .= " NULL";
            } else {
                $colDef .= " NOT NULL";
            }

            if (isset($config['default'])) {
                $colDef .= " DEFAULT {$config['default']}";
            }

            $columnDefs[] = $colDef;
        }

        // Add timestamp fields
        if ($timestamps) {
            $columnDefs[] = "created_at DATETIME NULL";
            $columnDefs[] = "updated_at DATETIME NULL";
        }

        // Add soft delete field
        if ($softDeletes) {
            $columnDefs[] = "deleted_at DATETIME NULL";
        }

        // Create table using raw SQL
        $sql = "CREATE TABLE {$tableName} (" . implode(", ", $columnDefs) . ")";
        $this->db->query($sql);
    }

    /**
     * Check if table exists
     */
    protected function tableExists(string $tableName): bool
    {
        $result = $this->db->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name=?",
            [$tableName]
        );
        return $result->getNumRows() > 0;
    }

    /**
     * Drop test table
     */
    protected function dropTestTable(string $tableName): void
    {
        $this->db->query("DROP TABLE IF EXISTS {$tableName}");
    }

    /**
     * Insert test data into table
     */
    protected function insertTestData(string $tableName, array $data): int
    {
        return $this->db->table($tableName)->insert($data);
    }

    /**
     * Insert multiple test records
     */
    protected function insertMultiple(string $tableName, array $dataArray): void
    {
        foreach ($dataArray as $data) {
            $this->db->table($tableName)->insert($data);
        }
    }

    /**
     * Get a test model instance
     */
    protected function getTestModel(string $modelClass)
    {
        $model = new $modelClass();
        $model->setConnection($this->db);
        return $model;
    }

    /**
     * Assert table has expected column
     */
    protected function assertTableHasColumn(string $tableName, string $columnName): void
    {
        $fields      = $this->db->getFieldData($tableName);
        $columnNames = array_column($fields, 'name');

        $this->assertContains($columnName, $columnNames, "Table '{$tableName}' should have column '{$columnName}'");
    }

    /**
     * Assert table record count
     */
    protected function assertTableCount(string $tableName, int $expectedCount, ?string $whereClause = null): void
    {
        $builder = $this->db->table($tableName);

        if ($whereClause) {
            $builder->where($whereClause);
        }

        $count = $builder->countAllResults();
        $this->assertEquals($expectedCount, $count, "Table '{$tableName}' should have {$expectedCount} records");
    }

    /**
     * Get single record from table
     */
    protected function getTableRecord(string $tableName, array $where = [])
    {
        $builder = $this->db->table($tableName);

        foreach ($where as $field => $value) {
            $builder->where($field, $value);
        }

        return $builder->limit(1)->get()->getRowArray();
    }
}

