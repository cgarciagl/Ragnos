# Primeros Pasos: Hola Mundo

En este tutorial crearemos un módulo funcional completo (CRUD) para gestionar una lista de "Tareas" en menos de 5 minutos.

## 1. Preparar la Base de Datos

Primero, necesitamos una tabla física donde guardar los datos. Ejecuta este SQL en tu base de datos:

```sql
CREATE TABLE `tareas` (
  `id_tarea` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text,
  `estado` enum('pendiente','en_progreso','completada') DEFAULT 'pendiente',
  `fecha_vencimiento` date DEFAULT NULL,
  PRIMARY KEY (`id_tarea`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 2. Generar el Controlador

Abre tu terminal en la carpeta raíz del proyecto y usa el generador de Ragnos:

```bash
php spark ragnos:make Proceso/Tareas -table tareas
```

Esto creará el archivo `app/Controllers/Proceso/Tareas.php`.

## 3. Configurar el Dataset

Abre el archivo recién creado `app/Controllers/Proceso/Tareas.php` y completa la configuración en el constructor. El generador habrá creado una estructura básica; edítala para que luzca así:

```php
namespace App\Controllers\Proceso;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Tareas extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        // 1. Título y Seguridad
        $this->checklogin(); // Requiere estar logueado
        $this->setTitle('Gestión de Tareas');

        // 2. Configuración de la Tabla
        $this->setTableName('tareas');
        $this->setIdField('id_tarea');

        // 3. Definición de Campos
        $this->addField('titulo', [
            'label' => 'Título de la Tarea',
            'rules' => 'required|min_length[5]'
        ]);

        $this->addField('descripcion', [
            'label' => 'Detalle',
            'type'  => 'textarea'
        ]);

        $this->addField('fecha_vencimiento', [
            'label' => 'Vence el',
            'type'  => 'date'
        ]);

        $this->addField('estado', [
            'label' => 'Estado Actual',
            'type'  => 'select', // O 'enum'
            'options' => [
                'pendiente' => 'Pendiente',
                'en_progreso' => 'En Progreso',
                'completada' => 'Finalizada'
            ]
        ]);

        // 4. Configurar la Grilla (Listado)
        $this->setTableFields(['titulo', 'estado', 'fecha_vencimiento']);
    }
}
```

## 4. Probar en el Navegador

1. Abre tu navegador e ingresa al sistema.
2. Navega manualmente a la URL de tu controlador. Si tu URL base es `localhost/ragnos`, ve a:
   `http://localhost/ragnos/proceso/tareas`

¡Listo! Deberías ver:

- Un listado de tareas (vacío por ahora).
- Un botón "Nuevo" que abre un formulario.
- Funcionalidad completa de Guardar, Editar y Eliminar.

## Siguientes Pasos

- Agrega este nuevo módulo al menú lateral (ver [Personalización de UI](../frontend/personalizacion_ui.md)).
- Aprende sobre más tipos de campos en [Referencia de Campos](../datasets/campos.md).
