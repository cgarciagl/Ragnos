# 💾 Datasets en Ragnos

## ¿Qué es un Dataset?

Un dataset (`RDatasetController`) es el concepto central de Ragnos para el desarrollo **declarativo**. En lugar de escribir controladores, modelos y vistas para cada módulo CRUD, un dataset permite definir la **estructura y comportamiento** de una entidad mediante metadatos en el constructor.

El framework utiliza esta definición para generar automáticamente:

- Interfaces de usuario (Formularios y Grillas/Listados).
- Validaciones de entrada (Backend y Frontend).
- Consultas SQL y persistencia en base de datos.
- Respuestas para APIs.

## Ventajas de los Datasets

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
        $this->checkLogin();
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

- `checkLogin()` para requerir sesión activa.
- `setTitle()` para título del módulo y breadcrumbs.

!!! tip "Seguridad por defecto"

    Siempre llama a `checkLogin()` al principio del constructor si tu módulo debe ser privado. Si olvidas esta línea, el módulo será accesible públicamente.

## Tabla y clave primaria

- `setTableName('table')` define la tabla principal.
  !!! warning "Coherencia con la BD"

      El valor de `setTableName` debe coincidir exactamente con el nombre de la tabla física en tu base de datos. Ragnos no crea la tabla por ti; asume que ya existe.

- `setIdField('id')` establece la clave primaria.
- `setAutoIncrement(true|false)` controla si la PK es autoincremental.

## Control de Operaciones

Es posible restringir qué operaciones CRUD están permitidas en el dataset mediante los siguientes métodos:

- `setCanInsert(bool)`: Habilita o deshabilita la creación de nuevos registros.
- `setCanUpdate(bool)`: Habilita o deshabilita la edición de registros existentes.
- `setCanDelete(bool)`: Habilita o deshabilita la eliminación de registros.

Esto es útil para tablas de solo lectura, catálogos históricos o datos sensibles que no deben modificarse ni borrarse.

### Ejemplo de uso

```php
// Deshabilitar la eliminación de registros
$this->setCanDelete(false);

// Dataset de solo lectura (solo visualización)
$this->setCanInsert(false);
$this->setCanUpdate(false);
$this->setCanDelete(false);
```

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

## Relaciones entre datasets (`addSearch`)

- Para relaciones más complejas tipo cabecera-lineas, consulta la guía de [Maestro-Detalle](maestro-detalle.md).

La función `addSearch(campoLocal, 'Namespace\Del\DatasetRelacionado')` es una herramienta poderosa que conecta dos datasets.

### Búsqueda Contextual Inteligente

Al vincular un campo con otro dataset, Ragnos habilita automáticamente **búsquedas contextuales**. Esto significa que el criterio de búsqueda no se limita solo al ID o al campo principal, sino que se extiende a **todos los campos visibles** definidos en el `setTableFields()` del dataset relacionado.

**Ejemplo:**
Imagina que estás en el módulo de **Pagos** (`Tienda/Pagos`) y necesitas seleccionar un **Cliente**.
Si en el dataset de **Clientes** (`Tienda/Clientes`) definiste:

```php
$this->setTableFields([
    'customerName',
    'Contacto', // Campo calculado: concat(contactLastName, ', ', contactFirstName)
    'salesRepEmployeeNumber' // Empleado a cargo
]);
```

Cuando busques un cliente desde el formulario de Pagos, podrás escribir:

- Una parte del **Nombre de la empresa** (`customerName`).
- El **Nombre del contacto** (`Contacto`).
- O incluso el **Nombre del empleado** a cargo.

Ragnos buscará coincidencias en cualquiera de esos campos definidos en el dataset destino, ofreciendo una experiencia de usuario mucho más flexible y potente sin escribir SQL adicional.

### Agrupación Automática en Reportes

Otra ventaja clave es que los campos asociados mediante `addSearch` se convierten automáticamente en **criterios de agrupación** disponibles en el generador de reportes. Esto permite agrupar métricas (como ventas totales) por cualquiera de los criterios de búsqueda (ej. Ventas por "Empleado a cargo" del cliente) sin configuración extra.

### Ventajas de `addSearch`

- **Reutilización**: Define la lógica de "cómo buscar un cliente" una sola vez en el dataset de Clientes, y úsalo en Pagos, Órdenes, etc.
- **Sin Joins Manuales**: El framework gestiona las consultas subyacentes.
- **UX Superior**: Selectores intuitivos que buscan por múltiples atributos relevantes.

### Prevención de Referencias Circulares

Al utilizar la función `addSearch()` para relacionar datasets, es de vital importancia asegurar que no se introduzcan referencias circulares, ya sean de manera directa o indirecta. Una referencia circular directa ocurre cuando un dataset **A** define una búsqueda hacia un dataset **B**, y a su vez, el dataset **B** define una búsqueda de vuelta hacia el dataset **A**. Esto genera un bucle infinito que puede saturar los recursos del servidor y provocar que el sistema falle inesperadamente al intentar resolver la dependencia mutua.

El problema tiende a ser aún más difícil de detectar cuando se trata de referencias circulares indirectas. Por ejemplo, el dataset **A** se relaciona con **B**, el dataset **B** con **C**, y finalmente **C** establece una relación mediante `addSearch()` que apunta de regreso al dataset **A**. Esta cadena de dependencias ocultas no solo afecta los tiempos de carga, sino que rompe completamente las consultas SQL automatizadas y procesos de agrupación que el framework intenta resolver en segundo plano.

Para evitar este inconveniente que compromete la estabilidad de la aplicación, se recomienda planificar proactivamente la arquitectura de los datos antes de implementar las relaciones. Es fundamental revisar el diseño de los módulos y mantener una jerarquía estricta (por ejemplo, de catálogos maestros hacia transacciones u operaciones dependientes), asegurando que el flujo de dependencias avance en una sola dirección y nunca retorne a un punto de origen ya evaluado.

## Configuración de la grilla

- `setTableFields([...])` define columnas visibles en el listado (DataTable).

!!! info "Importancia del primer campo"

    El **primer campo** que se agrega en `setTableFields()` es muy relevante, pues es el campo que se usará como **"descripción" del registro**. Este es el texto que aparece al seleccionar el registro en los controles de búsqueda, o en los dropdowns asociados con este catálogo. Por ello es importante seleccionarlo muy bien, y de hecho puede ser un campo calculado que combine dos o más campos, como en el caso del catálogo de "empleados" donde se ha usado el `nombreCompleto` como primer campo.

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
