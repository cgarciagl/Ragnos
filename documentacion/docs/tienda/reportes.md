# Controlador Reportes

El controlador `Reportes` extiende de `BaseController` y está diseñado para generar reportes relacionados con las ventas en la tienda. A continuación, se describen sus características principales:

## Métodos

### ventaspormes

Este método genera un reporte de las ventas por mes de los últimos 12 meses. A continuación, se detallan los pasos realizados:

1. **Verificación de inicio de sesión**:

   - Se asegura que el usuario haya iniciado sesión mediante `checklogin()`.

2. **Carga del helper**:

   - Carga el helper `ragnos_helper` ubicado en `App\ThirdParty\Ragnos\Helpers`.

3. **Obtención de datos**:

   - Utiliza el modelo `Dashboard` para obtener los datos de ventas de los últimos 12 meses mediante el método `ventasultimos12meses()`.

4. **Formateo de datos**:

   - Recorre los datos obtenidos y formatea el campo `Total` como moneda utilizando la función `moneyFormat`.

5. **Configuración del reporte**:

   - Crea una instancia de `RSimpleLevelReport`.
   - Configura el reporte con el título `Ventas por mes`, los datos obtenidos y las columnas `Mes` y `Total`.
   - Desactiva la visualización de totales con `setShowTotals(false)`.

6. **Renderizado y vista**:
   - Renderiza el contenido del reporte y lo envía a la vista `admin/reporte_view`.

## Resumen

El controlador `Reportes` está diseñado para generar reportes personalizados, como el de ventas por mes, utilizando herramientas específicas como `RSimpleLevelReport` y helpers para formatear los datos. Este controlador es esencial para la visualización de datos analíticos en la tienda.
