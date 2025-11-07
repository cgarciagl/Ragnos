# Controlador Empleados

El controlador `Empleados` gestiona las operaciones relacionadas con los empleados de la tienda. Extiende de `RDatasetController` y configura una tabla específica para este recurso.

## Configuración

- **Título**: `Empleados`.
- **Tabla asociada**: `employees`.
- **Campo ID**: `employeeNumber`.
- **Auto Increment**: Desactivado.
- **Campos adicionales**:
  - `nombreCompleto`: Campo de solo lectura que concatena `lastName` y `firstName`.
  - `employeeNumber`: Configurado con la etiqueta `Número de empleado` y las reglas `required|is_unique`.
  - `lastName`: Configurado con la etiqueta `Apellido` y la regla `required`.
  - `firstName`: Configurado con la etiqueta `Nombre` y la regla `required`.
  - `extension`: Configurado con la etiqueta `Extension`.
  - `email`: Configurado con la etiqueta `Email` y el tipo `email`.
  - `officeCode`: Configurado con la etiqueta `Oficina`, la regla `required` y un marcador de posición `Selecciona una oficina`.
  - `jobTitle`: Configurado con la etiqueta `Puesto` y la regla `required`.
  - `reportsTo`: Configurado como un campo desplegable con opciones generadas dinámicamente.
- **Campos de tabla**: `nombreCompleto`, `employeeNumber`, `officeCode`, `reportsTo`.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y asegura que el usuario esté autenticado. Inicializa los campos, las búsquedas y los menús desplegables.

### initializeFields()

Configura los campos del controlador, incluyendo etiquetas, reglas y tipos.

### initializeSearch()

Configura las búsquedas relacionadas, como `officeCode` relacionado con el controlador `Tienda\Oficinas`.

### initializeDropdown()

Configura el campo desplegable `reportsTo` con opciones generadas dinámicamente a partir de la tabla `employees`.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
