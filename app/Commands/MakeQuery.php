<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

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
        $sql = $this->getOption('query');
        if (empty($sql)) {
            $sql = CLI::prompt('Escribe la Consulta SQL (ej. SELECT id, nombre FROM mi_tabla)');
        }

        // 3. Conectar a BD y validar query (obtener metadata)
        $db = \Config\Database::connect();

        try {
            // Ejecutamos con limite 0 para no traer datos pero si el esquema
            $queryResult = $db->query($sql . " LIMIT 0");
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

            // Generar configuración del campo
            $config = $this->mapFieldType($field);

            // Construir el string del array PHP
            $fieldBody = "[\n";
            foreach ($config as $key => $val) {
                $fieldBody .= "            '$key' => '$val',\n";
            }
            $fieldBody .= "        ]";

            $generatedFields[] = "\$this->addField('{$field->name}', $fieldBody);";

            // Agregar a la grilla por defecto (los primeros 5)
            if (count($gridFields) < 5) {
                $gridFields[] = "'{$field->name}'";
            }
        }

        // 5. Generar el contenido del archivo
        $template = $this->getTemplate($namespace, $className, $sql, $primaryKey, $generatedFields, $gridFields);

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

    /**
     * Mapea tipos de SQL a Tipos/Reglas de Ragnos (Reutilizado de MakeDataset)
     */
    private function mapFieldType($field)
    {
        $config = [
            'label' => ucfirst(str_replace('_', ' ', $field->name)),
            'type'  => 'text',
        ];

        // Detección de tipos
        $type = strtolower($field->type ?? 'varchar');

        if (in_array($type, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'integer'])) {
            $config['type'] = 'number';
        } elseif (in_array($type, ['decimal', 'float', 'double', 'numeric', 'real'])) {
            $config['type'] = 'money';
        } elseif (in_array($type, ['date'])) {
            $config['type'] = 'date';
        } elseif (in_array($type, ['datetime', 'timestamp'])) {
            $config['type'] = 'datetime';
        } elseif (in_array($type, ['text', 'mediumtext', 'longtext'])) {
            $config['type'] = 'textarea';
        }

        return $config;
    }

    /**
     * Plantilla del archivo PHP para RQueryController
     */
    private function getTemplate($ns, $class, $sql, $pk, $fields, $grid)
    {
        $fieldsBlock = implode("\n\n        ", $fields);
        $gridBlock   = implode(", ", $grid);
        $sqlSafe     = str_replace("'", "\'", $sql); // Escapar comillas simples para el string PHP

        return <<<EOT
<?php

namespace {$ns};

use App\ThirdParty\Ragnos\Controllers\RQueryController;

class {$class} extends RQueryController
{
    public function __construct()
    {
        parent::__construct();

        // Configuración General
        \$this->checkLogin();
        \$this->setTitle('{$class}');
        
        // Configuración de Consulta
        \$this->setQuery("{$sqlSafe}");
        \$this->setIdField('{$pk}');

        // Definición de Campos (Generado Automáticamente)
        {$fieldsBlock}

        // Configuración de la Grilla (DataTables)
        \$this->setTableFields([{$gridBlock}]);
    }
}
EOT;
    }
}
