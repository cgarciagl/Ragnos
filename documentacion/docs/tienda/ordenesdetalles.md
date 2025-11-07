# Controlador Ordenesdetalles

El controlador `Ordenesdetalles` gestiona las operaciones relacionadas con los detalles de las órdenes de compra. Extiende de `RDatasetController` y configura una tabla específica para este recurso.

## Configuración

- **Título**: `Detalles de orden`.
- **Tabla asociada**: `orderdetails`.
- **Campo ID**: `idDetail`.
- **Campos adicionales**:
  - `orderNumber`: Configurado con la etiqueta `Número de orden`, la regla `required`, el valor predeterminado `master` y el tipo `hidden`.
  - `productCode`: Configurado con la etiqueta `Producto`, la regla `required` y relacionado con el controlador `Tienda\Productos`.
  - `quantityOrdered`: Configurado con la etiqueta `Cantidad ordenada` y la regla `required`.
  - `priceEach`: Configurado con la etiqueta `Precio unitario` y las reglas `required|money`.
- **Campos de tabla**: `productCode`, `quantityOrdered`, `priceEach`.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y asegura que el usuario esté autenticado.

### \_filters()

Aplica un filtro a la consulta del modelo para que solo se incluyan los detalles de la orden actual (`orderNumber`).

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
