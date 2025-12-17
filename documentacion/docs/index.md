# Ragnos Framework

## Guía práctica para desarrolladores

Esta documentación describe cómo utilizar **Ragnos** a través de su componente central: `RDatasetController`. Está dirigida a programadores PHP con conocimientos de CodeIgniter 4 que desean construir aplicaciones CRUD completas de forma declarativa, consistente y escalable.

---

## 1. ¿Qué es `RDatasetController`?

`RDatasetController` es un controlador base que permite definir **datasets** (conjuntos de datos) mediante configuración, sin necesidad de escribir lógica CRUD manual.

Un dataset describe:

- La tabla de base de datos
- Su clave primaria
- Los campos editables y visibles
- Reglas de validación
- Relaciones con otros datasets
- Comportamiento ante eventos (hooks)

El controlador concreto **no implementa lógica**, solo **declara intención**.

---

## 2. Crear un controlador de dataset

Ejemplo básico:

```php
namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Clientes extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();
    }
}
```

A partir de aquí, toda la funcionalidad se define dentro del constructor.

---

## 3. Seguridad y contexto

### Validar sesión

```php
$this->checklogin();
```

Impide el acceso al dataset sin sesión activa.

### Título del módulo

```php
$this->setTitle('Clientes');
```

Se utiliza en vistas, breadcrumbs y encabezados.

---

## 4. Configuración del dataset

### Tabla y clave primaria

```php
$this->setTableName('customers');
$this->setIdField('customerNumber');
$this->setAutoIncrement(false);
```

Define la persistencia del dataset.

---

## 5. Definición de campos

### Campo simple

```php
$this->addField('customerName', [
    'label' => 'Nombre',
    'rules' => 'required'
]);
```

Cada campo puede definir:

- `label`: texto visible
- `rules`: reglas de validación (CI4 + custom)

---

### Campo calculado / virtual

```php
$this->addField('Contacto', [
    'label' => 'Contacto',
    'rules' => 'readonly',
    'query' => "concat(contactLastName, ', ', contactFirstName)",
    'type'  => 'hidden'
]);
```

Características:

- No se guarda en la tabla
- Se genera vía SQL
- Ideal para listados

---

### Reglas especiales

Ejemplo:

```php
$this->addField('creditLimit', [
    'label' => 'Límite de crédito',
    'rules' => 'required|money'
]);
```

Ragnos incluye validadores personalizados como `money`, además de los nativos de CI4.

---

## 6. Relaciones entre datasets

### addSearch

```php
$this->addSearch('salesRepEmployeeNumber', 'Tienda\\Empleados');
```

Esto crea una relación lógica entre datasets:

- Selectores dinámicos
- Búsqueda asistida
- Reutilización de módulos

No requiere JOINs manuales.

---

## 7. Configuración de la grilla

```php
$this->setTableFields([
    'customerName',
    'Contacto',
    'salesRepEmployeeNumber'
]);
```

Define qué campos aparecen en el listado (DataTable).

---

## 8. Ciclo de vida del dataset (Hooks)

Ragnos expone eventos para reaccionar a cambios.

### Hooks disponibles

- `_beforeInsert()`
- `_afterInsert()`
- `_beforeUpdate()`
- `_afterUpdate()`
- `_beforeDelete()`
- `_afterDelete()`

---

### Ejemplo real

```php
function _afterUpdate()
{
    if (fieldHasChanged('creditLimit')) {
        $cache = \Config\Services::cache();
        $cache->delete('estadosdecuenta');
    }
}
```

#### `fieldHasChanged()`

Permite detectar si un campo fue modificado en la operación actual.

---

## 9. Tipos de campo soportados

Ragnos define los campos de un dataset mediante `addField()`. Cada campo acepta un conjunto de opciones que determinan **cómo se valida, cómo se muestra y cómo se persiste**.

---

### 9.1 Campo de texto (string)

```php
$this->addField('customerName', [
    'label' => 'Nombre',
    'rules' => 'required'
]);
```

Uso típico:

- Nombres
- Descripciones cortas
- Códigos

Características:

- Input tipo texto
- Validación estándar CI4

---

### 9.2 Campo numérico

```php
$this->addField('postalCode', [
    'label' => 'Código postal',
    'rules' => 'required|numeric'
]);
```

Uso típico:

- Códigos
- Cantidades
- Identificadores visibles

---

### 9.3 Campo monetario (`money`)

```php
$this->addField('creditLimit', [
    'label' => 'Límite de crédito',
    'rules' => 'required|money'
]);
```

Características:

- Validador personalizado de Ragnos
- Normalización de formato
- Preparado para UI financiera

Ideal para:

- Precios
- Límites
- Importes

---

### 9.4 Campo de solo lectura (`readonly`)

```php
$this->addField('Contacto', [
    'label' => 'Contacto',
    'rules' => 'readonly'
]);
```

Características:

- Visible pero no editable
- No se valida en escritura
- Útil para datos informativos

---

### 9.5 Campo oculto (`hidden`)

```php
$this->addField('Contacto', [
    'label' => 'Contacto',
    'rules' => 'readonly',
    'type'  => 'hidden'
]);
```

Características:

- No se muestra en formularios
- Puede mostrarse en grillas
- Frecuente en campos calculados

---

### 9.6 Campo calculado / virtual (`query`)

```php
$this->addField('Contacto', [
    'label' => 'Contacto',
    'rules' => 'readonly',
    'query' => "concat(contactLastName, ', ', contactFirstName)",
    'type'  => 'hidden'
]);
```

Características:

- No existe físicamente en la tabla
- Se genera vía SQL
- Solo lectura
- Ideal para UX en listados

---

### 9.7 Campo relacionado (lookup / search)

Este tipo se define indirectamente mediante `addSearch()`:

```php
$this->addSearch('salesRepEmployeeNumber', 'Tienda\Empleados');
```

Características:

- Relación entre datasets
- Selector dinámico
- No requiere JOIN manual

Uso típico:

- Claves foráneas
- Relaciones lógicas

---

### 9.8 Campo clave primaria

```php
$this->setIdField('customerNumber');
$this->setAutoIncrement(false);
```

Características:

- No editable por defecto
- Controla operaciones CRUD
- Puede ser autoincremental o manual

---

### 9.9 Campos no persistentes

Campos con `query` o sin correspondencia directa en la tabla:

- No se insertan
- No se actualizan
- Solo se calculan en lectura

Perfectos para:

- Resúmenes
- Campos concatenados
- Valores derivados

---

### 9.10 Resumen rápido

| Tipo de campo | Persistente | Editable | Uso principal       |
| ------------- | ----------- | -------- | ------------------- |
| Texto         | Sí          | Sí       | Datos generales     |
| Numérico      | Sí          | Sí       | Códigos, cantidades |
| Money         | Sí          | Sí       | Finanzas            |
| Readonly      | Depende     | No       | Informativo         |
| Hidden        | Depende     | No       | Técnico / UX        |
| Calculado     | No          | No       | Visualización       |
| Relación      | Sí          | Sí       | Claves foráneas     |

---

## 10. Cache e integración con servicios

Los hooks permiten integrarse con:

- Cache
- Logs
- Servicios externos
- Auditoría

Ejemplo:

```php
$cache = \Config\Services::cache();
$cache->delete('mi_clave');
```

---

## 10. Qué NO se escribe en Ragnos

Con `RDatasetController` **no necesitas**:

- Modelos CRUD
- Controladores con SQL
- Formularios manuales
- Validaciones duplicadas
- Vistas por módulo

Todo se genera a partir de la metadata.

---

## 11. Filosofía de diseño

Ragnos promueve:

- Declaración sobre implementación
- Consistencia visual y funcional
- Productividad
- Bajo mantenimiento
- Escalabilidad modular

Un controlador describe **qué es un dataset**, no **cómo funciona**.

---

## 12. Recomendaciones finales

- Diseña primero tu base de datos
- Un dataset = una tabla principal
- Usa campos virtuales para UX
- Aprovecha `addSearch` para relaciones
- Centraliza lógica en hooks

---

## 13. Próximos pasos

- Crear nuevos datasets
- Extender validadores
- Integrar reportes
- Optimizar cache por eventos

---

**Ragnos convierte el CRUD en configuración y al programador en arquitecto del dominio.**
