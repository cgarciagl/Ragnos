<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\ThirdParty\Ragnos\Generators\ControllerGenerator;

class MakeQuery extends BaseCommand
{
    /**
     * El grupo bajo el que aparecerá el comando al ejecutar 'php spark list'
     */
    protected $group = 'Ragnos';

    /**
     * El nombre del comando para ejecutar en terminal
     */
    protected $name = 'ragnos:make:query';

    /**
     * Descripción corta
     */
    protected $description = 'Genera un RQueryController a partir de una consulta SQL.';

    /**
     * Definición de argumentos y opciones
     */
    protected $usage = 'ragnos:make:query [ControllerName] [options]';
    protected $arguments = [
        'name' => 'El nombre de la clase del controlador (ej. Dashboard/Ventas)',
    ];
    protected $options = [
        '-query' => 'La consulta SQL (entre comillas)',
    ];

    public function run(array $params)
    {
        $generator = new ControllerGenerator();

        // 1. Obtener nombre del controlador
        $controllerName = array_shift($params);

        if (empty($controllerName)) {
            $controllerName = CLI::prompt('Nombre del Controlador (ej. Dashboard/Ventas)');
        }

        // Normalizar rutas y namespaces
        $pathParts       = explode('/', str_replace('\\', '/', $controllerName));
        $className       = array_pop($pathParts);
        $namespaceSuffix = implode('\\', $pathParts);
        $namespace       = 'App\\Controllers' . ($namespaceSuffix ? '\\' . $namespaceSuffix : '');

        // 2. Obtener Query
        $sql = CLI::getOption('query');
        if (empty($sql)) {
            $sql = CLI::prompt('Escribe la Consulta SQL (ej. SELECT id, nombre FROM mi_tabla)');
        }

        // 3. Conectar a BD y validar query (obtener metadata)
        $db = \Config\Database::connect();

        try {
            // Ejecutamos con limite 0 para no traer datos pero si el esquema
            $queryResult = $db->query($generator->normalizeMetadataQuery($sql));
            $fields      = $queryResult->getFieldData();
        } catch (\Throwable $th) {
            CLI::error("Error ejecutando la consulta: " . $th->getMessage());
            return;
        }

        CLI::write("Analizando resultado de la consulta...", 'yellow');

        $primaryKey      = 'id'; // Placeholder o intentar detectar
        $generatedFields = [];
        $gridFields      = [];

        // 4. Lógica de Mapeo
        foreach ($fields as $field) {

            // Para queries, no siempre hay primary_key marcada en el metadata del result set
            // pero si existe la propiedad, la usamos.
            if (isset($field->primary_key) && $field->primary_key) {
                $primaryKey = $field->name;
            }

            $generatedFields[] = [
                'name'   => $field->name,
                'config' => $generator->mapQueryField($field),
            ];

            // Agregar a la grilla por defecto (los primeros 5)
            if (count($gridFields) < 5) {
                $gridFields[] = $field->name;
            }
        }

        // 5. Generar el contenido del archivo
        $template = $this->generateControllerSource(
            $namespace,
            $className,
            $sql,
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
            CLI::write('¡Éxito! Query Dataset creado en:', 'green');
            CLI::write($filePath);
            CLI::write('Recuerda ajustar las etiquetas y reglas según sea necesario.', 'white');
        } else {
            CLI::error("Error al escribir el archivo.");
        }
    }

    public function generateControllerSource(
        string $namespace,
        string $className,
        string $query,
        string $primaryKey,
        array $fields,
        array $gridFields,
    ): string {
        return (new ControllerGenerator())->renderQueryController(
            $namespace,
            $className,
            $query,
            $primaryKey,
            $fields,
            $gridFields,
        );
    }

}
