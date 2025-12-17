# Datasets en Ragnos

## Crear un dataset

Un dataset es un controlador que extiende `RDatasetController`. Toda la configuración se realiza en el constructor y declara la metadata del módulo sin implementar lógica CRUD explícita.

### Ejemplo mínimo

```php
namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Clientes extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        // Seguridad y contexto
        $this->checklogin();
        $this->setTitle('Clientes');

        // Persistencia
        $this->setTableName('customers');
        $this->setIdField('customerNumber');
        $this->setAutoIncrement(false);

        // Campos
        $this->addField('customerName', [
            'label' => 'Nombre',
            'rules' => 'required'
        ]);

        // Campo calculado
        $this->addField('Contacto', [
            'label' => 'Contacto',
            'rules' => 'readonly',
            'query' => "concat(contactLastName, ', ', contactFirstName)",
            'type'  => 'hidden'
        ]);

        // Relaciones
        $this->addSearch('salesRepEmployeeNumber', 'Tienda\\Empleados');

        // Grilla
        $this->setTableFields([
            'customerName',
            'Contacto',
            'salesRepEmployeeNumber'
        ]);
    }
}
```

## Seguridad y contexto

- `checklogin()` para requerir sesión activa.
- `setTitle()` para título del módulo y breadcrumbs.

## Tabla y clave primaria

- `setTableName('table')` define la tabla principal.
- `setIdField('id')` establece la clave primaria.
- `setAutoIncrement(true|false)` controla si la PK es autoincremental.

## Definición de campos

Use `addField(name, options)` para describir validación, presentación y persistencia.
Opciones habituales:

- `label`: texto visible.
- `rules`: reglas de validación (CI4 + validadores Ragnos como `money`, `readonly`).
- `type`: `text`, `hidden`, etc.
- `query`: expresión SQL para campos virtuales.

Ejemplos:

- Campo simple: required, texto.
- Campo numérico: `numeric`.
- Monetario: `rules => 'required|money'`.
- Readonly / Hidden: visible en grilla, no editable.
- Calculado: `query => "concat(...)"` (no se persiste).

## Relaciones entre datasets

- `addSearch(localField, 'Namespace\\Dataset')` crea selectores y búsqueda asistida reutilizando otro dataset.
- No requiere joins manuales; facilita selectores dinámicos y reutilización de lógica.

## Configuración de la grilla

- `setTableFields([...])` define columnas visibles en el listado (DataTable).
- Puede incluir campos virtuales y etiquetas.
- `setSortingFields([...])` para ordenar por defecto.

## Ciclo de vida (Hooks)

Implementa hooks para reaccionar a eventos:

- `_beforeInsert()`, `_afterInsert()`
- `_beforeUpdate()`, `_afterUpdate()`
- `_beforeDelete()`, `_afterDelete()`

Ejemplo:

```php
function _afterUpdate()
{
    if (fieldHasChanged('creditLimit')) {
        $cache = \Config\Services::cache();
        $cache->delete('estadosdecuenta');
    }
}
```

## Tipos de campo soportados (resumen)

- Texto, Numérico, Money (validador `money`), Readonly, Hidden, Calculado (`query`), Relación (vía `addSearch`), Clave primaria.
- Campos con `query` o sin columna física no se insertan ni actualizan; solo se calculan en lectura.

## Cache e integración

Hooks permiten integrar cache, logs, servicios externos y auditoría:

```php
$cache = \Config\Services::cache();
$cache->delete('mi_clave');
```

## Uso de \_customFormDataFooter

- Propósito: permitir insertar HTML/JS adicional al pie del formulario de datos del dataset (por ejemplo paneles con detalles, resúmenes, botones extra).
- Cómo se implementa: el método debe devolver una cadena HTML. Normalmente se devuelve una vista con view('ruta/a/vista', $data).
- Datos disponibles: puede leer campos del formulario mediante $this->request->getPost(...) o construir datos a partir del id actual para pasarlos a la vista.
- Contenido de la vista: puede incluir HTML, fragmentos de plantilla y scripts que usen utilidades del framework (por ejemplo llamadas AJAX a rutas del mismo controlador para cargar detalles dinámicos).
- Buenas prácticas:
  - Mantener la lógica pesada en controladores/modelos y pasar solo datos preparados a la vista.
  - Evitar exponer información sensible en el HTML; comprobar permisos con checklogin()/roles antes de mostrar acciones.
  - Usar rutas internas del controlador para operaciones AJAX (p. ej. calcular totales, listar detalles) y gestionar errores HTTP/JSON.
  - Minimizar dependencias globales en la vista; preferir funciones utilitarias del proyecto.
- Ejemplo (conceptual):

```php
// en el dataset
function _customFormDataFooter()
{
        $id = $this->request->getPost($this->getIdField());
        $data = ['id' => $id, 'canEdit' => $this->hasPermission('edit')];
        return view('miModulo/footer_extra', $data);
}
```

- Resultado: la vista se renderiza debajo del formulario principal y se envía al cliente junto con el formulario, permitiendo mejorar la UX con información contextual (detalles, totales, históricos).
- Debug y mantenimiento: loguear errores servidor/JS y validar respuestas AJAX antes de manipular el DOM.

## Qué NO se escribe en Ragnos

Con `RDatasetController` no necesitas:

- Modelos CRUD manuales
- Controladores con SQL explícito
- Formularios y validaciones duplicadas
  Todo se genera desde la metadata del dataset.

## Recomendaciones

- Diseña primero la base de datos.
- Un dataset = una tabla principal.
- Usa campos virtuales para mejorar UX.
- Aprovecha `addSearch` para relaciones reutilizables.
- Centraliza lógica en hooks.

## Próximos pasos

- Crear nuevos datasets siguiendo este patrón.
- Extender validadores y tipos.
- Integrar reportes y optimizar cache por eventos.
- Mantener la metadata consistente para escalabilidad.
