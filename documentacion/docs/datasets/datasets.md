# Datasets en Ragnos

## ¿Qué es un Dataset?

Un dataset (`RDatasetController`) es el concepto central de Ragnos para el desarrollo **declarativo**. En lugar de escribir controladores, modelos y vistas para cada módulo CRUD, un dataset permite definir la **estructura y comportamiento** de una entidad mediante metadatos en el constructor.

El framework utiliza esta definición para generar automáticamente:

- Interfaces de usuario (Formularios y Grillas/Listados).
- Validaciones de entrada (Backend y Frontend).
- Consultas SQL y persistencia en base de datos.
- Respuestas para APIs.

## Ventajas

- **Centralización**: Todo (validación, display, persistencia) se define en un solo lugar.
- **Productividad**: Elimina la necesidad de escribir HTML repetitivo o consultas CRUD básicas.
- **Consistencia**: Todos los módulos se comportan y lucen igual.
- **Flexibilidad**: Extensiones mediante Hooks y campos virtuales.

## Crear un dataset

Un dataset es un controlador que extiende `RDatasetController`. Toda la configuración se realiza en el constructor.

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
Para una referencia completa de todas las opciones y tipos de campo, consulta la [Guía de Campos](campos.md).

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

- Para relaciones más complejas tipo cabecera-lineas, consulta la guía de [Maestro-Detalle](maestro-detalle.md).
- `addSearch(localField, 'Namespace\\Dataset')` crea selectores y búsqueda asistida reutilizando otro dataset.
- No requiere joins manuales; facilita selectores dinámicos y reutilización de lógica.

## Configuración de la grilla

- `setTableFields([...])` define columnas visibles en el listado (DataTable).
- Puede incluir campos virtuales y etiquetas.
- `setSortingFields([...])` para ordenar por defecto.

## Ciclo de vida (Hooks)

Puedes intervenir en el ciclo de vida de los datos y de la interfaz mediante métodos protegidos.

👉 **[Ver la Guía Completa de Hooks y Eventos](../avanzado/hooks.md)**

## Tipos de campo soportados (resumen)

- Texto, Numérico, Money (validador `money`), Readonly, Hidden, Calculado (`query`), Relación (vía `addSearch`), Clave primaria.
- Campos con `query` o sin columna física no se insertan ni actualizan; solo se calculan en lectura.

## Qué NO se escribe en Ragnos

Con `RDatasetController` no necesitas:

- Modelos CRUD manuales
- Controladores con SQL explícito
- Formularios y validaciones duplicadas
  Todo se genera desde la metadata del dataset.
  Buenas Prácticas y Recomendaciones

- **Diseño First**: Diseña primero la base de datos de manera sólida.
- **Un dataset = una tabla**: Cada dataset debe gestionar una tabla principal. Si necesitas vistas complejas, crea un `RQueryController`.
- **Campos Virtuales**: Usa `query` en `addField` para conciliar columnas (ej. nombre completo) en buscar/listar sin desnormalizar la BD.
- **Centralización**: Si una validación es regla de negocio, ponla en `rules` del dataset, no en el cliente.
- **Desacoplamiento**: Usa los hooks para limpiar caché o loguear, pero evita poner lógica de negocio pesada directamente en el controlador; llama a Servicios o Libreríaa relaciones reutilizables.
- Centraliza lógica en hooks.

## Próximos pasos

- Crear nuevos datasets siguiendo este patrón.
- Extender validadores y tipos.
- Integrar reportes y optimizar cache por eventos.
- Mantener la metadata consistente para escalabilidad.
