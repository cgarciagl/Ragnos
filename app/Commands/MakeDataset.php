<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\ThirdParty\Ragnos\Generators\ControllerGenerator;

class MakeDataset extends BaseCommand
{
    /**
     * El grupo bajo el que aparecerá el comando al ejecutar 'php spark list'
     */
    protected $group = 'Ragnos';

    /**
     * El nombre del comando para ejecutar en terminal
     */
    protected $name = 'ragnos:make';

    /**
     * Descripción corta
     */
    protected $description = 'Genera un RDatasetController a partir de una tabla de base de datos.';

    /**
     * Definición de argumentos y opciones
     */
    protected $usage = 'ragnos:make [ControllerName] [options]';
    protected $arguments = [
        'name' => 'El nombre de la clase del controlador (ej. Tienda/Productos)',
    ];
    protected $options = [
        '-table' => 'El nombre de la tabla en la BD (si es diferente al nombre del controlador)',
    ];

    public function run(array $params)
    {
        $generator = new ControllerGenerator();

        // 1. Obtener nombre del controlador
        $controllerName = array_shift($params);

        if (empty($controllerName)) {
            $controllerName = CLI::prompt('Nombre del Controlador (ej. Tienda/Clientes)');
        }

        // Normalizar rutas y namespaces
        $pathParts       = explode('/', str_replace('\\', '/', $controllerName));
        $className       = array_pop($pathParts);
        $namespaceSuffix = implode('\\', $pathParts);
        $namespace       = 'App\\Controllers' . ($namespaceSuffix ? '\\' . $namespaceSuffix : '');

        // 2. Obtener tabla de BD
        $tableName = CLI::getOption('table');
        if (empty($tableName)) {
            // Intentar adivinar el nombre de la tabla (plural minúsculas) si no se provee
            $tableName = CLI::prompt('Nombre de la Tabla en BD', strtolower($className));
        }

        // 3. Conectar a BD y obtener metadata
        $db = \Config\Database::connect();

        if (!$db->tableExists($tableName)) {
            CLI::error("Error: La tabla '{$tableName}' no existe en la base de datos.");
            return;
        }

        CLI::write("Analizando esquema de la tabla '{$tableName}'...", 'yellow');

        $fields          = $db->getFieldData($tableName);
        $primaryKey      = 'id'; // Default
        $generatedFields = [];
        $gridFields      = [];

        // 4. Lógica de Mapeo (Intelligence Logic)
        foreach ($fields as $field) {

            // Detectar PK
            if ($field->primary_key) {
                $primaryKey = $field->name;
                continue; // Generalmente no agregamos la PK como campo editable, saltar
            }

            $generatedFields[] = [
                'name'   => $field->name,
                'config' => $generator->mapDatasetField($field),
            ];

            // Agregar a la grilla por defecto (limitar a los primeros 5 para no saturar)
            if (count($gridFields) < 5) {
                $gridFields[] = $field->name;
            }
        }

        // 5. Generar el contenido del archivo
        $template = $this->generateControllerSource(
            $namespace,
            $className,
            $tableName,
            $primaryKey,
            $generatedFields,
            $gridFields,
        );

        // 6. Guardar archivo
        $savePath = APPPATH . 'Controllers/' . ($namespaceSuffix ? $namespaceSuffix . '/' : '');

        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }

        $filePath = $savePath . $className . '.php';

        if (file_exists($filePath)) {
            CLI::error("Error: El archivo ya existe en {$filePath}");
            return;
        }

        helper('file');
        if (write_file($filePath, $template)) {
            CLI::write('¡Éxito! Dataset creado en:', 'green');
            CLI::write($filePath);
            CLI::write('Recuerda ajustar las etiquetas y reglas según sea necesario.', 'white');
        } else {
            CLI::error("Error al escribir el archivo.");
        }
    }

    public function generateControllerSource(
        string $namespace,
        string $className,
        string $tableName,
        string $primaryKey,
        array $fields,
        array $gridFields,
    ): string {
        return (new ControllerGenerator())->renderDatasetController(
            $namespace,
            $className,
            $tableName,
            $primaryKey,
            $fields,
            $gridFields,
        );
    }

}
