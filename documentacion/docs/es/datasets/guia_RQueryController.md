# Guía de Referencia: RQueryController

`RQueryController` es una variante especializada del controlador base de Ragnos diseñada para **consultas de solo lectura** y **búsquedas avanzadas**.

A diferencia de [`RDatasetController`](datasets.md) (que se mapea a una tabla física para hacer CRUD), `RQueryController` se mapea a una **Sentencia SQL Arbitraria**.

---

## 🎯 ¿Cuándo usar RQueryController?

Usa este controlador cuando:

1.  **No necesitas editar:** Solo quieres listar, buscar y filtrar datos.
2.  **La data es compleja:** Necesitas hacer `JOINs`, subconsultas o campos calculados (ej. `CONCAT`, `CASE WHEN`) que no existen en una sola tabla física.
3.  **Reportes Rápidos:** Quieres exponer una vista de base de datos en el sistema administrativo rápidamente.

---

## 🧬 Estructura Básica

Un controlador de consulta extiende de `RQueryController` y define su lógica en el constructor.

**Ejemplo Analizado:** `Searchusuarios.php`

```php
namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RQueryController;

class Searchusuarios extends RQueryController
{
    public function __construct()
    {
        parent::__construct();

        // 1. Seguridad
        $this->checkUserInGroup('Administrador');

        // 2. Configuración Visual
        $this->setTitle('🔎 Usuarios');

        // 3. La Fuente de Datos (SQL)
        $this->setQuery("SELECT usu_id, usu_nombre as 'Nombre', usu_login as 'Login', usu_activo as Activo, usu_grupo FROM gen_usuarios");

        // 4. Clave Primaria (Virtual o Real)
        $this->setIdField('usu_id');

        // 5. Configuración de Campos (Filtros y Visualización)
        $this->addField('Activo', [
            'label'   => 'Activo',
            'type'    => 'dropdown',
            'options' => ['S' => 'SI', 'N' => 'NO'],
        ]);

        $this->addField('usu_grupo', ['label' => 'Grupo']);

        // 6. Relaciones para Búsqueda
        $this->addSearch('usu_grupo', 'Gruposdeusuarios');

        // 7. Definición de la Grilla
        $this->setTableFields(['Nombre', 'Login', 'Activo', 'usu_grupo']);
    }
}
```

---

## 📚 Métodos Clave

### `setQuery(string $sql)`

Define la fuente de datos.

- **Importante:** No uses `ORDER BY` o `LIMIT` aquí; Ragnos los inyecta dinámicamente según la interacción del usuario en la grilla.
- **Alias:** Es muy recomendable usar alias SQL (`AS 'Nombre'`) para que los encabezados de la tabla sean legibles automáticamente.

### `setIdField(string $field)`

Aunque sea una consulta personalizada, Ragnos necesita saber cuál es la columna que identifica unívocamente a cada fila (para selección, detalles, etc.).

- Debe estar presente en el `SELECT` de `setQuery`.

### `addField(string $name, array $config)`

Funciona igual que en `RDatasetController`, pero aquí se usa principalmente para **generar los filtros de búsqueda**.

- Si defines un campo como `'type' => 'dropdown'`, Ragnos creará un `<select>` en la barra de búsqueda avanzada.

### `addSearch(string $field, string $controller)`

Permite vincular una columna de tu consulta con otro controlador Ragnos.

- Esto habilita el icono de lupa para buscar valores relacionados.

### `checkUserInGroup(string|array $groups)`

Restringe el acceso a este controlador exclusivamente a los grupos de usuarios especificados.

---

## ⚠️ Diferencias con RDatasetController

| Característica            | RDatasetController        | RQueryController                 |
| :------------------------ | :------------------------ | :------------------------------- |
| **Fuente de Datos**       | `setTableName('tabla')`   | `setQuery('SELECT...')`          |
| **Edición (Save/Update)** | ✅ Soportado (Automático) | ❌ No soportado                  |
| **Eliminación (Delete)**  | ✅ Soportado              | ❌ No soportado                  |
| **Nuevo Registro**        | ✅ Soportado              | ❌ No soportado                  |
| **Uso Principal**         | CRUDs (Catálogos)         | Reportes, Dashboards, Buscadores |

---

## 💡 Tips de Implementación

1.  **Campos Calculados:**
    Puedes crear columnas "virtuales" en el SQL y usarlas en la grilla:

    ```php
    $this->setQuery("SELECT id, precio * cantidad AS Total, ... FROM ventas");
    $this->setTableFields(['id', 'Total']);
    ```

2.  **Uniones (JOINs):**
    Ideal para mostrar nombres en lugar de IDs sin configurar `addSearch` visualmente:
    ```php
    $this->setQuery("
        SELECT p.nombre, c.categoria_nombre
        FROM productos p
        JOIN categorias c ON p.cat_id = c.id
    ");
    ```
