# Controlador Productos

El controlador `Productos` extiende de `RDatasetController` y está diseñado para gestionar los productos en la tienda. A continuación, se describen sus características principales:

## Constructor

El constructor realiza las siguientes acciones:

- Llama al constructor de la clase padre `RDatasetController`.
- Verifica que el usuario haya iniciado sesión mediante `checklogin()`.
- Configura el título de la vista como `Productos`.
- Define la tabla de base de datos asociada como `products`.
- Especifica el campo identificador como `productCode`.
- Indica que el campo identificador no es autoincremental.

## Campos Configurados

El controlador define los siguientes campos para la tabla:

- **productName**: Nombre del producto. Reglas: `required`.
- **productCode**: Código del producto. Reglas: `required|is_unique`.
- **productLine**: Línea del producto. Reglas: `required`.
- **productScale**: Escala del producto. Reglas: `required`.
- **productVendor**: Proveedor del producto. Reglas: `required`.
- **productDescription**: Descripción del producto. Reglas: `required`. Tipo: `textarea`.
- **quantityInStock**: Cantidad en stock. Reglas: `required|numeric`.
- **buyPrice**: Precio de compra. Reglas: `required|numeric|money`.
- **MSRP**: Precio de Venta Sugerido. Reglas: `required|numeric|money`.

## Funcionalidades Adicionales

- **Búsqueda**: Permite buscar por `productLine` en el controlador `Tienda\Lineas`.
- **Campos de Tabla**: Los campos mostrados en la tabla son:
  - `productName`
  - `productCode`
  - `productLine`
  - `productVendor`
  - `quantityInStock`
  - `MSRP`

## Resumen

El controlador `Productos` está diseñado para gestionar los productos de la tienda, proporcionando validaciones y configuraciones específicas para cada campo, así como funcionalidades de búsqueda y visualización en tablas.
