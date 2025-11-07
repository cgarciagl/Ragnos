# Controlador Ordenes

El controlador `Ordenes` gestiona las operaciones relacionadas con las órdenes de compra de la tienda. Extiende de `RDatasetController` y configura una tabla específica para este recurso.

## Configuración

- **Título**: `Ordenes de compra`.
- **Tabla asociada**: `orders`.
- **Campo ID**: `orderNumber`.
- **Auto Increment**: Desactivado.
- **Campos adicionales**:
  - `orderNumber`: Configurado con la etiqueta `Número de orden` y las reglas `required|is_unique`.
  - `orderDate`: Configurado con la etiqueta `Fecha de orden`, la regla `required` y el tipo `date`.
  - `requiredDate`: Configurado con la etiqueta `Fecha requerida` y el tipo `date`.
  - `shippedDate`: Configurado con la etiqueta `Fecha de envío` y el tipo `date`.
  - `status`: Configurado como un campo desplegable con opciones como `Enviado`, `Resuelto`, `Cancelado`, entre otros.
  - `customerNumber`: Configurado con la etiqueta `Cliente`, la regla `required` y relacionado con el controlador `Tienda\Clientes`.
  - `comments`: Configurado con la etiqueta `Comentarios` y el tipo `textarea`.
  - `total`: Campo calculado que muestra el total de la orden.
- **Campos de tabla**: `orderNumber`, `orderDate`, `status`, `customerNumber`, `total`.
- **Ordenación**: Por `orderDate` en orden descendente.
- **Detalles habilitados**: Sí.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y asegura que el usuario esté autenticado.

### \_customFormDataFooter()

Devuelve una vista personalizada para el pie del formulario de órdenes.

### calculatotal()

Calcula el total de una orden específica. Realiza las siguientes acciones:

- Valida el número de orden recibido.
- Calcula el total sumando la cantidad ordenada por el precio de cada artículo.
- Devuelve el total en formato JSON o un error si ocurre algún problema.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
