# Tipos de campo soportados en Ragnos

En Ragnos, los campos de un dataset se definen mediante el método `addField()`. Este método es parte fundamental de la configuración de un [RDatasetController](datasets.md).
Cada campo es una **descripción declarativa** de cómo un atributo del dominio:

- Se valida
- Se muestra en formularios
- Se muestra en grillas
- Se persiste (o no) en base de datos

Ragnos utiliza esta metadata para generar automáticamente formularios, validaciones, listados y comportamiento CRUD.

---

## 1. Estructura general de `addField()`

```php
$this->addField('nombreCampo', [
    'label' => 'Etiqueta visible',
    'rules' => 'reglas|de|validacion',
    'type'  => 'tipo',
    'query' => 'expresion SQL'
]);
```

### Parámetros comunes

| Parámetro | Descripción                            |
| --------- | -------------------------------------- |
| `label`   | Texto visible en formularios y grillas |
| `rules`   | Reglas de validación (CI4 + Ragnos)    |
| `type`    | Tipo de campo (opcional)               |
| `query`   | Expresión SQL para campos calculados   |
| `tab`     | Nombre de la pestaña (opcional)        |

!!! tip "Reglas de Validación"

    Ragnos adopta el potente motor de validación de CodeIgniter 4. Puedes usar reglas como `required`, `is_unique`, `min_length`, `valid_email`, etc.

    [Consulta todas las reglas disponibles en la documentación oficial de CodeIgniter 4](https://codeigniter.com/user_guide/libraries/validation.html#available-rules)

!!! note "Validación `is_unique` Simplificada"

    A diferencia de CodeIgniter 4, donde `is_unique` suele requerir parámetros como `is_unique[tabla.campo]`, en Ragnos se ha simplificado su uso: **solo necesitas indicar la palabra clave `is_unique`**.

    El framework detecta automáticamente sobre qué tabla y campo se está operando (usando lo definido en `setTableName` y el nombre del campo en `addField`) para construir la consulta de verificación. Además, maneja correctamente las excepciones al editar un registro (autodetectando la clave primaria definida con `setIdField` para ignorar el registro actual).

    **Casos de uso comunes:**
    Es ideal para campos que deben ser únicos en todo el sistema, como:

    - Direcciones de **correo electrónico** en un registro de usuarios.
    - **Nombres de usuario** o alias.
    - **Códigos de producto** (SKU) o códigos de barras.
    - **DNI** o documentos de identidad.

### Organización en Pestañas

Ragnos permite organizar los campos del formulario en pestañas para mejorar la usabilidad en formularios extensos.
Simplemente agrega la clave `tab` al array de configuracion del campo con el nombre de la pestaña deseada.

```php
$this->addField('informacion_basica', [ ... , 'tab' => 'General' ]);
$this->addField('detalles_tecnicos', [ ... , 'tab' => 'Detalles' ]);
$this->addField('precios', [ ... , 'tab' => 'Finanzas' ]);
```

- Los campos que no tengan definida una pestaña irán automáticamente a una pestaña etiquetada como **"General"**.
- Si ningún campo tiene definida una pestaña, el formulario se mostrará sin pestañas (comportamiento clásico).
- Si ocurre un error de validación en un campo oculto por una pestaña, Ragnos activará automáticamente esa pestaña para mostrar el error.

---

## 2. Campo de texto (string)

```php
$this->addField('customerName', [
    'label' => 'Nombre',
    'rules' => 'required'
]);
```

- Persistente
- Editable
- Texto libre

---

## 3. Campo numérico

```php
$this->addField('postalCode', [
    'label' => 'Código postal',
    'rules' => 'required|numeric'
]);
```

- Persistente
- Editable
- Numérico

---

## 4. Campo monetario (`money`)

```php
$this->addField('creditLimit', [
    'label' => 'Límite de crédito',
    'rules' => 'required|money'
]);
```

- Validador custom
- Normalización automática
- Uso financiero

---

## 5. Campo readonly

```php
$this->addField('Contacto', [
    'label' => 'Contacto',
    'rules' => 'readonly'
]);
```

- Visible
- No editable

---

## 6. Campo hidden

```php
$this->addField('Contacto', [
    'label' => 'Contacto',
    'rules' => 'readonly',
    'type'  => 'hidden'
]);
```

- No visible en formularios
- Útil para UX

---

## 7. Campo calculado (`query`)

```php
$this->addField('Contacto', [
    'label' => 'Contacto',
    'rules' => 'readonly',
    'query' => "concat(contactLastName, ', ', contactFirstName)",
    'type'  => 'hidden'
]);
```

- No persistente
- SQL dinámico
- Solo lectura

---

## 8. Campo relacionado (`addSearch`)

```php
$this->addSearch('salesRepEmployeeNumber', 'Tienda\\Empleados');
```

- Clave foránea lógica
- Selector dinámico
- Sin JOIN manual

---

## 9. Campo clave primaria

```php
$this->setIdField('customerNumber');
```

- Identidad del dataset
- Obligatorio

- Por defecto, los campos Id se consideran autoincrementales. Si no es el caso, se debe incluir explícitamente `$this->setAutoIncrement(false);`.

---

## 10. Campo dropdown (enum)

- Breve descripción: un dropdown (enum) presenta un conjunto cerrado de opciones (clave => etiqueta). Se almacena la clave seleccionada en la base de datos; la etiqueta se usa solo para la interfaz.

- Configuración típica:
  - type: "dropdown"
  - options: array asociativo [valor => etiqueta]
  - default: clave por defecto
  - rules: validar con reglas CI4 (ej. required, in_list)
  - se recomienda para listas cortas y estables; para grandes volúmenes usar addSearch (selector relacionado).

- Ejemplo estático:

```php
$this->addField('status', [
        'label'   => 'Estado',
        'type'    => 'dropdown',
        'options' => [
                'shipped'   => 'Enviado',
                'pending'   => 'Pendiente',
                'cancelled' => 'Cancelado'
        ],
        'default' => 'pending',
        'rules'   => 'required|in_list[shipped,pending,cancelled]'
]);
```

- Ejemplo dinámico (cargar desde DB):

```php
$rows   = $this->db->table('categories')->select('id, name')->get()->getResultArray();
$options = array_column($rows, 'name', 'id');

$this->addField('categoryId', [
        'label'   => 'Categoría',
        'type'    => 'dropdown',
        'options' => $options,
        'rules'   => 'required|in_list[' . implode(',', array_keys($options)) . ']'
]);
```

- Placeholder / opción vacía: agregar una entrada con clave vacía para forzar selección:
  'options' => ['' => '(Seleccione)'] + $options

- Validación y seguridad:
  - Use in_list para evitar valores no permitidos.
  - Si las opciones son dinámicas, regenere la lista en cada carga de formulario para que la in_list coincida.

- UX y performance:
  - Dropdown para <~20-30 opciones.
  - Para relaciones con muchas filas, usar addSearch o un componente tipo autocompletar.
  - Si necesita seleccionar múltiples valores, prefiera un componente multiselect (o un campo tipo relación); no use dropdown simple.

- Internacionalización: almacene claves estables y traduzca etiquetas en la generación de options para facilitar cambios de idioma.

- Persistencia: el valor guardado es la clave; si necesita guardar la etiqueta, considere un campo calculado o una vista.

## 11. Resumen

| Tipo      | Persistente | Editable |
| --------- | ----------- | -------- |
| Texto     | Sí          | Sí       |
| Numérico  | Sí          | Sí       |
| Money     | Sí          | Sí       |
| Readonly  | Depende     | No       |
| Hidden    | Depende     | No       |
| Calculado | No          | No       |
| Relación  | Sí          | Sí       |
| Dropdown  | Sí          | Sí       |
| Clave PK  | Sí          | Sí       |

---

**En Ragnos, los campos son declaraciones del dominio, no simples inputs.**
