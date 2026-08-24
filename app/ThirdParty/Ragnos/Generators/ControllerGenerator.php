<?php

namespace App\ThirdParty\Ragnos\Generators;

use InvalidArgumentException;

/**
 * Builds Ragnos controller metadata and source code without side effects.
 */
final class ControllerGenerator
{
    public function mapDatasetField(object $field): array
    {
        $name = (string) ($field->name ?? 'field');
        $type = strtolower((string) ($field->type ?? 'varchar'));
        $config = [
            'label' => ucfirst(str_replace('_', ' ', $name)),
            'type'  => 'text',
            'rules' => 'required',
        ];

        if ($type === 'tinyint' && (int) ($field->max_length ?? 0) === 1) {
            $config['type'] = 'checkbox';
            $config['rules'] = 'permit_empty';
        } elseif (in_array($type, ['int', 'smallint', 'mediumint', 'bigint', 'integer'], true)) {
            $config['type'] = 'number';
            $config['rules'] .= '|integer';
        } elseif (in_array($type, ['decimal', 'float', 'double', 'numeric'], true)) {
            $config['type'] = 'money';
            $config['rules'] .= '|decimal';
        } elseif ($type === 'date') {
            $config['type'] = 'date';
        } elseif (in_array($type, ['datetime', 'timestamp'], true)) {
            $config['type'] = 'datetime';
        } elseif (in_array($type, ['text', 'mediumtext', 'longtext'], true)) {
            $config['type'] = 'textarea';
        }

        if (in_array($type, ['varchar', 'char'], true) && (int) ($field->max_length ?? 0) > 0) {
            $config['rules'] .= '|max_length[' . (int) $field->max_length . ']';
        }

        if (str_contains(strtolower($name), 'image') || str_contains(strtolower($name), 'foto')) {
            $config['type'] = 'image';
            $config['rules'] = 'permit_empty';
        }

        return $config;
    }

    public function mapQueryField(object $field): array
    {
        $type = strtolower((string) ($field->type ?? 'varchar'));
        $config = [
            'label' => ucfirst(str_replace('_', ' ', (string) ($field->name ?? 'field'))),
            'type'  => 'text',
        ];

        if (in_array($type, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'integer'], true)) {
            $config['type'] = 'number';
        } elseif (in_array($type, ['decimal', 'float', 'double', 'numeric', 'real'], true)) {
            $config['type'] = 'money';
        } elseif ($type === 'date') {
            $config['type'] = 'date';
        } elseif (in_array($type, ['datetime', 'timestamp'], true)) {
            $config['type'] = 'datetime';
        } elseif (in_array($type, ['text', 'mediumtext', 'longtext'], true)) {
            $config['type'] = 'textarea';
        }

        return $config;
    }

    public function normalizeMetadataQuery(string $query): string
    {
        $normalized = rtrim(trim($query), " \t\r\n;");

        if ($normalized === '') {
            throw new InvalidArgumentException('La consulta SQL no puede estar vacía.');
        }

        return $normalized . ' LIMIT 0';
    }

    public function renderDatasetController(
        string $namespace,
        string $className,
        string $tableName,
        string $primaryKey,
        array $fields,
        array $gridFields,
    ): string {
        return $this->renderController(
            $namespace,
            $className,
            'RDatasetController',
            $this->renderDatasetConfiguration($tableName, $primaryKey, $fields, $gridFields),
        );
    }

    public function renderQueryController(
        string $namespace,
        string $className,
        string $query,
        string $primaryKey,
        array $fields,
        array $gridFields,
    ): string {
        $this->assertPhpIdentifier($className, 'class name');
        $this->assertNamespace($namespace);

        return $this->renderController(
            $namespace,
            $className,
            'RQueryController',
            "        \$this->setQuery(" . var_export($query, true) . ");\n"
            . "        \$this->setIdField(" . var_export($primaryKey, true) . ");\n\n"
            . $this->renderFields($fields)
            . "\n        // Configuración de la Grilla (DataTables)\n"
            . "        \$this->setTableFields(" . $this->renderStringList($gridFields) . ");",
        );
    }

    public function renderFieldStatement(string $fieldName, array $config): string
    {
        $options = [];
        foreach ($config as $key => $value) {
            $options[] = '            ' . var_export((string) $key, true) . ' => ' . var_export((string) $value, true) . ',';
        }

        return "        \$this->addField(" . var_export($fieldName, true) . ", [\n"
            . implode("\n", $options)
            . "\n        ]);";
    }

    private function renderDatasetConfiguration(string $tableName, string $primaryKey, array $fields, array $gridFields): string
    {
        return "        \$this->setTableName(" . var_export($tableName, true) . ");\n"
            . "        \$this->setIdField(" . var_export($primaryKey, true) . ");\n"
            . "        // \$this->setAutoIncrement(true); // Descomentar si la PK es AI\n\n"
            . $this->renderFields($fields)
            . "\n        // Configuración de la Grilla (DataTables)\n"
            . "        \$this->setTableFields(" . $this->renderStringList($gridFields) . ");";
    }

    private function renderFields(array $fields): string
    {
        $statements = [];
        foreach ($fields as $field) {
            $statements[] = is_string($field) ? $field : $this->renderFieldStatement($field['name'], $field['config']);
        }

        return implode("\n\n", $statements);
    }

    private function renderStringList(array $values): string
    {
        return '[' . implode(', ', array_map(static fn($value): string => var_export((string) $value, true), $values)) . ']';
    }

    private function renderController(string $namespace, string $className, string $parentClass, string $configuration): string
    {
        $this->assertPhpIdentifier($className, 'class name');
        $this->assertNamespace($namespace);

        return "<?php\n\nnamespace {$namespace};\n\n"
            . "use App\\ThirdParty\\Ragnos\\Controllers\\{$parentClass};\n\n"
            . "class {$className} extends {$parentClass}\n{\n"
            . "    public function __construct()\n    {\n"
            . "        parent::__construct();\n\n"
            . "        // Configuración General\n"
            . "        \$this->checkLogin();\n"
            . "        \$this->setTitle(" . var_export($className, true) . ");\n\n"
            . $configuration . "\n"
            . "    }\n}\n";
    }

    private function assertPhpIdentifier(string $value, string $label): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new InvalidArgumentException("Invalid {$label}: {$value}");
        }
    }

    private function assertNamespace(string $namespace): void
    {
        foreach (explode('\\', $namespace) as $segment) {
            $this->assertPhpIdentifier($segment, 'namespace segment');
        }
    }
}
