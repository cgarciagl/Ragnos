# Controlador Oficinas

El controlador `Oficinas` gestiona las operaciones relacionadas con las oficinas de la tienda. Extiende de `RDatasetController` y configura una tabla específica para este recurso.

## Configuración

- **Título**: `Oficinas`.
- **Tabla asociada**: `offices`.
- **Campo ID**: `officeCode`.
- **Campos adicionales**:
  - `nombreCiudad`: Campo de solo lectura que concatena `city` y `country`.
  - `city`: Configurado con la etiqueta `Ciudad` y las reglas `required|is_unique`.
  - `officeCode`: Configurado con la etiqueta `Código` y las reglas `required|is_unique`.
  - `phone`: Configurado con la etiqueta `Teléfono` y la regla `required`.
  - `addressline1`: Configurado con la etiqueta `Dirección 1` y la regla `required`.
  - `addressline2`: Configurado con la etiqueta `Dirección 2`.
  - `state`: Configurado con la etiqueta `Estado`.
  - `country`: Configurado con la etiqueta `País` y la regla `required`.
  - `postalcode`: Configurado con la etiqueta `Código postal` y la regla `required`.
  - `territory`: Configurado con la etiqueta `Territorio` y la regla `required`.
- **Campos de tabla**: `nombreCiudad`, `state`, `territory`.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y asegura que el usuario esté autenticado.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
