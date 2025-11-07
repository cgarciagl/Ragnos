# Controlador Clientes

El controlador `Clientes` gestiona las operaciones relacionadas con los clientes de la tienda. Extiende de `RDatasetController` y configura una tabla específica para este recurso.

## Configuración

- **Título**: `Clientes`.
- **Tabla asociada**: `customers`.
- **Campo ID**: `customerNumber`.
- **Auto Increment**: Desactivado.
- **Campos adicionales**:
  - `customerName`: Configurado con la etiqueta `Nombre` y la regla `required`.
  - `contactLastName`: Configurado con la etiqueta `Apellido de contacto` y la regla `required`.
  - `contactFirstName`: Configurado con la etiqueta `Nombre de contacto` y la regla `required`.
  - `Contacto`: Campo de solo lectura que concatena `contactLastName` y `contactFirstName`.
  - `phone`: Configurado con la etiqueta `Teléfono` y la regla `required`.
  - `addressLine1`: Configurado con la etiqueta `Dirección 1` y la regla `required`.
  - `addressLine2`: Configurado con la etiqueta `Dirección 2`.
  - `city`: Configurado con la etiqueta `Ciudad` y la regla `required`.
  - `state`: Configurado con la etiqueta `Estado`.
  - `postalCode`: Configurado con la etiqueta `Código postal` y la regla `required`.
  - `country`: Configurado con la etiqueta `País` y la regla `required`.
  - `salesRepEmployeeNumber`: Configurado con la etiqueta `Empleado a cargo` y relacionado con el controlador `Tienda\Empleados`.
  - `creditLimit`: Configurado con la etiqueta `Límite de crédito` y las reglas `required|money`.
- **Campos de tabla**: `customerName`, `Contacto`, `salesRepEmployeeNumber`.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y asegura que el usuario esté autenticado.

### \_afterUpdate()

Se ejecuta después de actualizar un cliente. Si el campo `creditLimit` ha cambiado, elimina el caché de `estadosdecuenta`.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
