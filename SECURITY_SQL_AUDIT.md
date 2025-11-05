# Auditoría de Seguridad SQL — Ragnos

Fecha inicial: 2025-11-04
Última actualización: 2025-11-05
Alcance: carpeta `app/` del proyecto (CodeIgniter 4)

## Resumen ejecutivo

**Estado actual:** Persiste 1 riesgo crítico y algunos riesgos medios.

### Riesgos actuales:

- **Crítico** (⚠️ PENDIENTE): Inyección via `sFilter` en `SearchFilterTrait` (WHERE crudo sin validación).
- **Medio** (⚠️ PENDIENTE): APIs que ejecutan SQL crudo (`queryToAssocArray`, `getCachedData`, `setOrderBy` genérico) sin variantes con parámetros.

## Hallazgos por archivo

### 1) `app/ThirdParty/Ragnos/Models/Traits/SearchFilterTrait.php`

- **Riesgo CRÍTICO** (⚠️ PENDIENTE) — WHERE crudo desde cliente:

  - **Código:** Líneas 88-92 - `sFilter` se decodifica y se pasa directo a `where($filter, NULL, FALSE)`.
    ```php
    $filter = base64_decode($request->getPost('sFilter'));
    $filter = str_replace("'", "''", $filter); // Escape single quotes
    if ($filter) {
        $this->builder()->where($filter, NULL, FALSE);
    }
    ```
  - **Efecto:** Un atacante puede inyectar condiciones arbitrarias (funciones, subconsultas, operadores). Aunque se duplican comillas simples, no es suficiente protección contra inyecciones que no usen comillas (números, funciones SQL, operadores lógicos).
  - **Recomendación:**
    - Eliminar el paso de texto crudo. Aceptar un JSON de filtros estructurados (p.ej. `[ { field, op, value } ]`) y construir con Query Builder (`where`, `whereIn`, `like`, etc.).
    - Si se mantiene texto, validar con whitelist estricta de campos/operadores y tokenizar; nunca pasar el string crudo a `where(..., false)`.

### 2) `app/ThirdParty/Ragnos/Models/RTableModel.php`

- **`setOrderBy($orderby)` acepta cadena libre** (⚠️ PENDIENTE):
  - **Estado actual:** No se ha implementado validación. La API sigue aceptando cualquier cadena.
  - Uso observado en `RReportLib` con campos provenientes de `realField()` (control del backend). Sin embargo, la API es peligrosa si se reutiliza con entrada de usuario.
  - **Recomendación:** Exponer `setOrderByField($field, $dir = 'asc')` con whitelist de campos/direcciones; o validar `orderby` (solo nombres de columna permitidos y `ASC|DESC`).

### 3) `app/ThirdParty/Ragnos/Helpers/utiles_helper.php`

- **`queryToAssocArray($sql)` ejecuta SQL crudo** (⚠️ PENDIENTE):

  - **Estado actual:** No se ha agregado variante con parámetros. La función sigue ejecutando SQL directamente sin binding.
  - Uso actual: `Tienda/Empleados.php` con SQL estático (seguro).
  - **Recomendación:** Agregar variante con parámetros `queryToAssocArrayParams($sql, $params, ...)` y documentar que no se use con entradas del usuario sin binding.

- **`getCachedData($sql)` ejecuta SQL crudo y cachea** (⚠️ PENDIENTE):
  - **Estado actual:** No se ha agregado variante con parámetros.
  - Usos en `App/Models/Dashboard.php` con SQL estático (seguros).
  - **Recomendación:** Proveer `getCachedDataParams($sql, $params, $ttl)`.

## Recomendaciones priorizadas

### Prioridad CRÍTICA:

1. **SearchFilterTrait:** Remover uso de `sFilter` crudo y parsear `filters` JSON estructurado con validación de campos/operadores.

### Prioridad MEDIA:

2. **Helpers:** Añadir variantes con parámetros (`queryToAssocArrayParams`, `getCachedDataParams`) y documentar los métodos crudos como "no usar con input de usuario".
3. **RTableModel:** Nueva API `setOrderByField` con validación; mantener `setOrderBy` solo para uso interno.

## Riesgos residuales y notas

- **Mientras `sFilter` crudo exista, cualquier endpoint que lo procese será vulnerable** independientemente de otras mitigaciones.
- Evitar `where($sql, NULL, FALSE)` salvo en expresiones internas estáticas y probadas.
- Documentar contratos del frontend (DataTables/Reportes) para alinear el modelo de filtros seguro.
