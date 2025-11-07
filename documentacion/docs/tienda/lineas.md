# Controlador Lineas

El controlador `Lineas` gestiona las operaciones relacionadas con las líneas de productos de la tienda. Extiende de `RDatasetController` y configura una tabla específica para este recurso.

## Configuración

- **Título**: `Lineas de productos`.
- **Tabla asociada**: `productlines`.
- **Campo ID**: `productLine`.
- **Auto Increment**: Desactivado.
- **Campos adicionales**:
  - `productLine`: Configurado con la etiqueta `Línea de producto` y las reglas `required|is_unique`.
  - `textDescription`: Configurado con la etiqueta `Descripción`, la regla `required` y el tipo `textarea`.
  - `htmlDescription`: Configurado con la etiqueta `Descripción HTML` y el tipo `htmltextarea`.
- **Campos de tabla**: `productLine`, `textDescription`.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y asegura que el usuario esté autenticado.

### \_beforeDelete()

Se ejecuta antes de eliminar una línea de productos. Lanza un error indicando que no se pueden eliminar líneas de productos.

### \_beforeUpdate(&$a)

Se ejecuta antes de actualizar una línea de productos. Lanza un error indicando que no se pueden modificar líneas de productos.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
