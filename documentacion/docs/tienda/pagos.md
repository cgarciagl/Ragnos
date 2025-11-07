# Controlador Pagos

El controlador `Pagos` gestiona las operaciones relacionadas con los pagos realizados por los clientes. Extiende de `RDatasetController` y configura una tabla específica para este recurso.

## Configuración

- **Título**: `Pagos`.
- **Tabla asociada**: `payments`.
- **Campo ID**: `idPayment`.
- **Campos adicionales**:
  - `customerNumber`: Configurado con la etiqueta `Cliente`, la regla `required` y relacionado con el controlador `Tienda\Clientes`.
  - `checkNumber`: Configurado con la etiqueta `Número de cheque` y la regla `required`.
  - `paymentDate`: Configurado con la etiqueta `Fecha de pago`, la regla `required` y el tipo `date`.
  - `amount`: Configurado con la etiqueta `Monto` y las reglas `required|numeric|money`.
- **Campos de tabla**: `customerNumber`, `checkNumber`, `paymentDate`, `amount`.
- **Ordenación**: Por `paymentDate` en orden descendente.

## Funciones

### \_\_construct()

Configura el controlador con las propiedades mencionadas anteriormente y asegura que el usuario esté autenticado.

## Constructor

El constructor inicializa las configuraciones específicas del controlador y asegura que las reglas de negocio se cumplan.
