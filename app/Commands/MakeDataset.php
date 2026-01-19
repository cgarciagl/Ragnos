<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

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
        $tableName = $this->getOption('table');
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

            // Generar configuración del campo
            $config = $this->mapFieldType($field);

            // Construir el string del array PHP
            $fieldBody = "[\n";
            foreach ($config as $key => $val) {
                $fieldBody .= "            '$key' => '$val',\n";
            }
            $fieldBody .= "        ]";

            $generatedFields[] = "\$this->addField('{$field->name}', $fieldBody);";

            // Agregar a la grilla por defecto (limitar a los primeros 5 para no saturar)
            if (count($gridFields) < 5) {
                $gridFields[] = "'{$field->name}'";
            }
        }

        // 5. Generar el contenido del archivo
        $template = $this->getTemplate($namespace, $className, $tableName, $primaryKey, $generatedFields, $gridFields);

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

        if (write_file($filePath, $template)) {
            CLI::write('¡Éxito! Dataset creado en:', 'green');
            CLI::write($filePath);
            CLI::write('Recuerda ajustar las etiquetas y reglas según sea necesario.', 'white');
        } else {
            CLI::error("Error al escribir el archivo.");
        }
    }

    /**
     * Mapea tipos de SQL a Tipos/Reglas de Ragnos
     */
    private function mapFieldType($field)
    {
        $config = [
            'label' => ucfirst(str_replace('_', ' ', $field->name)), // snake_case a Texto Legible
            'type'  => 'text', // Default
            'rules' => 'required' // Default safe
        ];

        // Detección de tipos
        $type = strtolower($field->type);

        // 1. Numéricos
        if (in_array($type, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'integer'])) {
            $config['type']   = 'number';
            $config['rules'] .= '|integer';
        } elseif (in_array($type, ['decimal', 'float', 'double', 'numeric'])) {
            $config['type']   = 'money'; // Asumimos dinero para decimales, seguro de cambiar
            $config['rules'] .= '|decimal';
        }

        // 2. Fechas
        elseif (in_array($type, ['date'])) {
            $config['type'] = 'date';
        } elseif (in_array($type, ['datetime', 'timestamp'])) {
            $config['type'] = 'datetime';
        }

        // 3. Textos Largos
        elseif (in_array($type, ['text', 'mediumtext', 'longtext'])) {
            $config['type'] = 'textarea';
        }

        // 4. Booleanos (Tinyint 1 a menudo es bool)
        elseif ($type === 'tinyint' && $field->max_length == 1) {
            $config['type']  = 'checkbox';
            $config['rules'] = 'permit_empty'; // Checkbox no marcado no envía valor a veces
        }

        // Ajustar max_length para varchars
        if ($type === 'varchar' || $type === 'char') {
            if ($field->max_length > 0) {
                $config['rules'] .= "|max_length[{$field->max_length}]";
            }
        }

        // Detectar posibles campos de imagen/archivo por nombre
        if (strpos($field->name, 'image') !== false || strpos($field->name, 'foto') !== false) {
            $config['type']  = 'image';
            $config['rules'] = 'permit_empty'; // Uploads suelen ser opcionales en update
        }

        return $config;
    }

    /**
     * Plantilla del archivo PHP
     */
    private function getTemplate($ns, $class, $table, $pk, $fields, $grid)
    {
        $fieldsBlock = implode("\n\n        ", $fields);
        $gridBlock   = implode(", ", $grid);

        return <<<EOT
<?php

namespace {$ns};

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class {$class} extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        // Configuración General
        \$this->checkLogin();
        \$this->setTitle('{$class}'); // TODO: Ajustar título
        
        // Configuración de Base de Datos
        \$this->setTableName('{$table}');
        \$this->setIdField('{$pk}');
        // \$this->setAutoIncrement(true); // Descomentar si la PK es AI

        // Definición de Campos (Generado Automáticamente)
        {$fieldsBlock}

        // Configuración de la Grilla (DataTables)
        \$this->setTableFields([{$gridBlock}]);
    }
}
EOT;
    }
}