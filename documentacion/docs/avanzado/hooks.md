# Hooks y ciclo de vida en Ragnos

En Ragnos, los **hooks** permiten ejecutar lógica personalizada en distintos momentos del ciclo de vida de un dataset.  
Son el mecanismo principal para **extender comportamiento** sin romper el modelo declarativo.

Los hooks se definen como métodos protegidos dentro del controlador que extiende `RDatasetController`.

---

## 1. ¿Qué es un hook?

Un hook es un método que Ragnos ejecuta automáticamente **antes o después** de una operación CRUD.

Sirven para:

- Validaciones adicionales
- Cálculos derivados
- Invalidación de cache
- Auditoría
- Integración con servicios externos

El controlador **no implementa CRUD**, solo reacciona a eventos.

---

## 2. Hooks disponibles

Ragnos expone los siguientes hooks:

| Hook              | Momento de ejecución              |
| ----------------- | --------------------------------- |
| `_beforeInsert()` | Antes de insertar un registro     |
| `_afterInsert()`  | Después de insertar un registro   |
| `_beforeUpdate()` | Antes de actualizar un registro   |
| `_afterUpdate()`  | Después de actualizar un registro |
| `_beforeDelete()` | Antes de eliminar un registro     |
| `_afterDelete()`  | Después de eliminar un registro   |

Todos los hooks son **opcionales**.

---

## 3. Estructura básica de un hook

```php
protected function _afterUpdate()
{
    // lógica personalizada
}
```

Reglas:

- Deben ser `protected`
- No retornan valores
- Acceden al estado interno del dataset

---

## 4. Hook `_beforeInsert()`

Se ejecuta **antes de un INSERT**.

### Usos comunes

- Inicializar valores
- Normalizar datos
- Validaciones adicionales

### Ejemplo

```php
protected function _beforeInsert()
{
    $this->data['created_at'] = date('Y-m-d H:i:s');
}
```

---

## 5. Hook `_afterInsert()`

Se ejecuta **después de un INSERT exitoso**.

### Usos comunes

- Logs
- Cache
- Notificaciones

### Ejemplo

```php
protected function _afterInsert()
{
    log_message('info', 'Registro creado');
}
```

---

## 6. Hook `_beforeUpdate()`

Se ejecuta **antes de un UPDATE**.

### Usos comunes

- Validaciones condicionales
- Control de cambios críticos

### Ejemplo

```php
protected function _beforeUpdate()
{
    if ($this->data['status'] === 'cerrado') {
        throw new \Exception('No se puede modificar un registro cerrado');
    }
}
```

---

## 7. Hook `_afterUpdate()`

Se ejecuta **después de un UPDATE exitoso**.

### Usos comunes

- Invalidar cache
- Recalcular datos dependientes
- Auditoría

### Ejemplo real

```php
protected function _afterUpdate()
{
    if (fieldHasChanged('creditLimit')) {
        \Config\Services::cache()->delete('estadosdecuenta');
    }
}
```

---

## 8. Hook `_beforeDelete()`

Se ejecuta **antes de un DELETE**.

### Usos comunes

- Validar dependencias
- Evitar borrados críticos

### Ejemplo

```php
protected function _beforeDelete()
{
    if ($this->hasRelations()) {
        throw new \Exception('No se puede eliminar el registro');
    }
}
```

---

## 9. Hook `_afterDelete()`

Se ejecuta **después de un DELETE**.

### Usos comunes

- Limpieza de recursos
- Cache
- Logs

### Ejemplo

```php
protected function _afterDelete()
{
    log_message('info', 'Registro eliminado');
}
```

---

## 10. Detección de cambios: `fieldHasChanged()`

Ragnos permite saber si un campo fue modificado durante un UPDATE.

### Uso

```php
if (fieldHasChanged('creditLimit')) {
    // acción específica
}
```

### Características

- Compara valor anterior vs nuevo
- Solo disponible en hooks de update
- Ideal para lógica condicional

---

## 11. Acceso a servicios de CodeIgniter

Desde un hook puedes acceder a servicios:

```php
$cache = \Config\Services::cache();
$logger = \Config\Services::logger();
```

Esto permite integrar:

- Cache
- Logs
- Email
- APIs externas

---

## 12. Interrupción segura: `raise()`

En ocasiones es necesario detener una operación CRUD debido a reglas de negocio específicas que van más allá de la validación de campos estándar. Para esto, Ragnos proporciona la función auxiliar `raise()`.

### ¿Qué hace `raise()`?

1. Interrumpe inmediatamente la ejecución del script.
2. Evita que la operación (INSERT, UPDATE, DELETE) se complete.
3. Muestra el mensaje de error al usuario en la interfaz gráfica.

### Ejemplos reales

**1. Proteger registros del sistema (evitar borrado)**

En este ejemplo, evitamos que se elimine el grupo de usuarios con ID 1 (Administradores):

```php
public function _beforeDelete()
{
    // Obtener el ID del registro que se está intentando borrar
    $id = oldValue('gru_id');

    if ($id == 1) {
        raise('No se puede borrar el grupo de administradores');
    }
}
```

**2. Validar integridad referencial manual**

A veces es preferible validar relaciones manualmente antes de dejar que la base de datos arroje un error SQL.

```php
private function checkAssociatedUsers($groupId)
{
    $db = db_connect();
    // Verificar si existen usuarios para este grupo
    $userCount = $db->table('gen_usuarios')->where('usu_grupo', $groupId)->countAllResults();

    if ($userCount > 0) {
        // Detiene el proceso y avisa al usuario
        raise('No se puede borrar porque tiene usuarios asociados');
    }
}
```

**3. Proteger registros contra edición**

Similar al borrado, podemos impedir modificaciones en registros críticos dentro del hook `_beforeUpdate`:

```php
public function _beforeUpdate(&$a)
{
    if (oldValue('gru_id') == 1) {
        raise('No se puede modificar el grupo de administradores');
    }
}
```

> **Nota:** `raise()` es la forma recomendada de lanzar errores de validación lógica ("Soft errors") que el usuario debe corregir o conocer, a diferencia de `throw new Exception` que podría interpretarse como un error del sistema ("Hard error").

---

## 13. Buenas prácticas

- Mantén los hooks pequeños y específicos
- No escribas lógica de negocio compleja
- Usa hooks para reacciones, no para flujo principal
- Prefiere `_afterUpdate()` para cache
- Documenta los efectos secundarios

---

## 14. Errores comunes

❌ Usar hooks para reemplazar lógica de controlador  
❌ Modificar demasiados campos dentro del hook  
❌ Depender del orden entre hooks  
❌ Ejecutar SQL manual innecesario

---

## 15. Filosofía

Los hooks en Ragnos permiten **extender sin romper**:

- No acoplan lógica al CRUD
- Mantienen el enfoque declarativo
- Facilitan mantenimiento

---

**En Ragnos, los hooks no controlan el flujo: reaccionan al dominio.**
