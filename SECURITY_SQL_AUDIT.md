# Auditoría de Seguridad SQL — Ragnos

Fecha: 2025-11-04
Alcance: carpeta `app/` del proyecto (CodeIgniter 4)

## Resumen ejecutivo

Se detectaron 1 riesgo crítico y varios riesgos altos/medios relacionados con construcción manual de SQL, filtrado crudo y APIs que aceptan consultas en texto. Los usos actuales más peligrosos permiten inyección SQL a través de parámetros de entrada no validados (ej. `sFilter`). Otras áreas usan builder y parámetros correctamente pero conviene reforzar casting/whitelists.

- Crítico: Inyección via `sFilter` en `SearchFilterTrait` (WHERE crudo).
- Alto: Búsquedas `LIKE` concatenadas en `SearchFilterTrait` e inclusión de subconsultas sin binding.
- Medio: APIs que ejecutan SQL crudo (`queryToAssocArray`, `getCachedData`, `setOrderBy` genérico). Actualmente se usan con SQL estático, pero son “footguns”.
- Bajo: `limit`/`offset` desde POST sin castear explícito (CI suele castear, pero mejorarlo).

## Metodología

- Búsqueda de patrones: `->query(`, `SELECT|INSERT|UPDATE|DELETE`, `orderBy($`, `like(`, accesos a `getPost()`.
- Revisión manual de traits/modelos/controladores y helpers de DB.

## Hallazgos por archivo

### 1) `app/ThirdParty/Ragnos/Models/Traits/SearchFilterTrait.php`

- Riesgo CRÍTICO — WHERE crudo desde cliente:

  - Código: `sFilter` se decodifica y se pasa directo a `where($filter, NULL, FALSE)`.
  - Efecto: un atacante puede inyectar condiciones arbitrarias (funciones, subconsultas, operadores) al no usar binding.
  - Recomendación:
    - Eliminar el paso de texto crudo. Aceptar un JSON de filtros estructurados (p.ej. `[ { field, op, value } ]`) y construir con Query Builder (`where`, `whereIn`, `like`, etc.).
    - Si se mantiene texto, validar con whitelist estricta de campos/operadores y tokenizar; nunca pasar el string crudo a `where(..., false)`.

- Riesgo ALTO — Concatenación SQL en búsquedas `LIKE`:

  - Ejemplos:
    - `return "($sql) LIKE '%$textForSearch%'";`
    - `return "$table.$fieldName LIKE '%$textForSearch%'";`
  - Problema: Aunque se duplican comillas simples, no hay binding; la condición completa se inserta como texto crudo. Además, depende del motor (Postgres/NoSQL).
  - Recomendación: Usar `builder->like($col, $text, 'both')` y agrupar con `groupStart()/groupEnd()` para múltiples campos. Mantener el valor como parámetro, y aplicar funciones (LOWER/CAST) solo a la columna cuando sea imprescindible.

- Riesgo RELACIONADO — SQL compilado ejecutado:
  - Patrón: `getCompiledSelect()` + `$db->query($sqlCompiled)`. Si previamente se inyectó condición cruda (vía `sFilter`), la consulta resultante hereda la vulnerabilidad.

### 2) `app/ThirdParty/Ragnos/Models/Traits/JsonResultTrait.php`

- Ordenamiento por índice (OK): se mapea el índice de columna de DataTables a campos definidos por backend → minimiza inyección en ORDER BY.
- Límite/offset desde POST: usar casteo explícito `(int)` al tomar `length` y `start` antes de `limit($limit, $offset)`.

### 3) `app/ThirdParty/Ragnos/Models/RTableModel.php`

- `setOrderBy($orderby)` acepta cadena libre.
  - Uso observado en `RReportLib` con campos provenientes de `realField()` (control del backend). Sin embargo, la API es peligrosa si se reutiliza con entrada de usuario.
  - Recomendación: Exponer `setOrderByField($field, $dir = 'asc')` con whitelist de campos/direcciones; o validar `orderby` (solo nombres de columna permitidos y `ASC|DESC`).

### 4) `app/ThirdParty/Ragnos/Helpers/utiles_helper.php`

- `queryToAssocArray($sql)` ejecuta SQL crudo.

  - Uso actual: `Tienda/Empleados.php` con SQL estático (seguro).
  - Recomendación: Agregar variante con parámetros `queryToAssocArrayParams($sql, $params, ...)` y documentar que no se use con entradas del usuario sin binding.

- `getCachedData($sql)` ejecuta SQL crudo y cachea por `md5($sql)`.
  - Usos en `App/Models/Dashboard.php` con SQL estático (seguros).
  - Recomendación: Proveer `getCachedDataParams($sql, $params, $ttl)`.

### 5) `app/Services/Admin_aut.php`

- Correcto: consulta parametrizada `$db->query($sql, [$id])`.

### 6) `app/Controllers/Admin.php`

- Búsqueda con Query Builder: `orLike($field, $searchTerm)` en campos definidos (OK). Añadir casteo `(int)` a `iDisplayLength`/`iDisplayStart`.

### 7) `app/Controllers/Searchusuarios.php` + `RQueryController`

- La base query es estática (OK) pero hereda riesgo por `sFilter` de `SearchFilterTrait` al componer la consulta final.

## Recomendaciones priorizadas

1. Eliminar `sFilter` crudo y migrar a filtros estructurados (JSON) con whitelist de campos y operadores.
2. Reescribir condiciones `LIKE` concatenadas por `builder->like()/orLike()` con binding.
3. Endurecer APIs:
   - Crear `queryToAssocArrayParams()` y `getCachedDataParams()` y deprecar el uso con entradas externas.
   - Limitar `setOrderBy` a campos/direcciones permitidos.
4. Castear explícitamente `length` y `start` a int en `JsonResultTrait` y controladores.
5. Añadir tests mínimos de seguridad (inyección simple en `sFilter`, payloads con `%` y `_` en búsquedas, y strings largos en `length/start`).

## Ejemplo de contrato de filtros estructurados

- Entrada (POST `filters` JSON):
  ```json
  [
    { "field": "usu_grupo", "op": "=", "value": 3 },
    { "field": "Nombre", "op": "like", "value": "juan" }
  ]
  ```
- Validación:
  - `field` ∈ `tablefields` o whitelist.
  - `op` ∈ { "=", "!=", "in", "like", "between" }.
  - Tipado por campo (numérico, texto, fecha) y saneado.
- Construcción:
  - `=`/`!=`: `builder->where($col, $val)`/`builder->where($col.' !=', $val)`.
  - `in`: `builder->whereIn($col, $arr)`.
  - `like`: `builder->like($col, $val, 'both')`.
  - `between`: `builder->where($col.' >=', $v1)->where($col.' <=', $v2)`.

## Plan sugerido de corrección (rápido)

- SearchFilterTrait:
  - Remover uso de `sFilter` crudo y parsear `filters` JSON.
  - Reemplazar retornos de string SQL por llamadas a `like()/orLike()` agrupadas.
- JsonResultTrait:
  - Castear `length`/`start` a int.
- RTableModel:
  - Nueva API `setOrderByField` con validación; mantener `setOrderBy` solo para uso interno.
- Helpers:
  - Añadir variantes con parámetros y anotar los métodos crudos como “no usar con input de usuario”.

## Riesgos residuales y notas

- Mientras `sFilter` crudo exista, cualquier endpoint que lo procese será vulnerable independientemente de otras mitigaciones.
- Evitar `where($sql, NULL, FALSE)` salvo en expresiones internas estáticas y probadas.
- Documentar contratos del frontend (DataTables/Reportes) para alinear el modelo de filtros seguro.

## Anexo: ocurrencias relevantes

- `->query(...)` directo:

  - `ThirdParty/Ragnos/Models/Traits/SearchFilterTrait.php` (via compiled select)
  - `ThirdParty/Ragnos/Helpers/utiles_helper.php` (`queryToAssocArray`, `getCachedData`)
  - `ThirdParty/Ragnos/Models/Traits/JsonResultTrait.php` (via compiled select)
  - `Services/Admin_aut.php` (parametrizada, OK)

- `ORDER BY` dinámico:

  - `RTableModel::setOrderBy` (API genérica, ojo usos futuros)
  - `JsonResultTrait::setOrderByForJsonResult` (segura por índice + mapping)

- Accessos POST relevantes:
  - `SearchFilterTrait`: `search[value]`, `sOnlyField`, `sFilter` (punto crítico)
  - `JsonResultTrait`: `order[0][column]`, `order[0][dir]`, `length`, `start`
  - `Admin`: parámetros de búsqueda y paginación

---

¿Quieres que prepare un PR con los cambios mínimos (quitar `sFilter` crudo y reescribir los `LIKE`), o prefieres que te proponga primero el contrato del nuevo filtro JSON para integrarlo con tu frontend?
