# Relación Maestro-Detalle en Ragnos (Guía para principiantes)

Esta guía explica cómo crear una pantalla donde tienes un registro principal (como una **Orden de compra**) y una lista de elementos relacionados (los **Detalles** o productos de esa orden).

La idea básica es tener dos controladores:

1. **El Maestro (Ordenes):** Controla la información general (fecha, cliente, total).
2. **El Detalle (OrdenesDetalles):** Controla la lista de productos dentro de esa orden.

---

## 1. Configurando el Maestro (Controlador `Ordenes`)

Este es el "padre" de la relación. Aquí definimos la cabecera de la factura.

- **Configuración básica:** Le decimos a Ragnos que use la tabla `orders` y que la clave principal es `orderNumber`.
- **Campos:** Definimos los campos normales como fecha (`orderDate`), estado (`status`) y cliente (`customerNumber`).
- **Campo Total (Calculado):** Para mostrar el total de la orden sin guardarlo manualmente, usamos una pequeña consulta SQL dentro de la configuración del campo. Esta consulta suma `cantidad * precio` de la tabla de detalles.
- **Activar el modo detalle:**
  Hay una línea clave que debes agregar en tu controlador maestro para avisar que tendrá "hijos":
  ```php
  $this->setHasDetails(true);
  ```
- **Mostrar la tabla de detalles:**
  Para que la lista de productos aparezca al final del formulario de la orden, usamos una "vista de pie de página" (`_customFormDataFooter`). Esta vista carga un archivo (por ejemplo, `ordenescustomfooter`) que contiene el hueco donde se dibujará la tabla.

## 2. Configurando el Detalle (Controlador `Ordenesdetalles`)

Este es el "hijo". Controla cada línea de producto.

- **El truco del campo oculto:**
  Necesitamos que cada producto sepa a qué orden pertenece. Para eso, en el campo `orderNumber` del detalle hacemos dos cosas:
  1. Lo ponemos como `hidden` (oculto) para que el usuario no lo toque.
  2. Le asignamos el valor por defecto `$this->master`.
     _¿Qué hace esto?_ Cuando creas un detalle desde la orden #100, Ragnos automáticamente rellena este campo con el número 100.
- **Filtrar los datos:**
  No queremos ver _todos_ los productos de _todas_ las órdenes. En el método `_filters()`, agregamos una regla para que solo se carguen los productos que coincidan con el ID del maestro actual (`$this->master`).
- **Actualizar cambios:**
  Usamos funciones especiales (llamadas _hooks_) como `_afterInsert` o `_afterUpdate` para limpiar la memoria caché. Esto asegura que si agregas un producto, el total de la orden principal se recalcule correctamente.

## 3. La Vista Mágica (`ordenescustomfooter.php`)

Este archivo es un pequeño trozo de HTML y JavaScript que conecta todo. Se coloca al final del formulario de la Orden.

```php
<hr />
<div class="row clearfix" id="panelorden">
    <div class="card text-bg-dark">
        <h5 class="card-header">Detalles</h5>
        <div class="card-body">
            <div id="detalleorden">

            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let orden = $("input[name='orderNumber']").val();
        if (orden == '') {
            $("#panelorden").remove();
        } else {
            RagnosUtils.showControllerTableIn('#detalleorden', 'tienda/ordenesdetalles', orden);
        }
    });
</script>
```

**¿Qué hace exactamente?**

1. **Crea un espacio vacío:** Dibuja un recuadro (un `div`) en la pantalla donde irán los detalles.
2. **Verifica si hay orden:**
   - Si estás creando una orden nueva (aún no tiene número), **oculta** el recuadro. No puedes agregar productos a una orden que no existe.
   - Si estás editando una orden existente, **muestra** el recuadro.
3. **Llama al controlador hijo:**
   Usa una función de JavaScript (`RagnosUtils.showControllerTableIn`) para "incrustar" la tabla del controlador de Detalles dentro del recuadro vacío. Le pasa el número de orden actual para que sepa qué filtrar.

## Resumen del flujo de trabajo

1. **Abres una Orden:** Ves los datos generales (Maestro).
2. **El sistema verifica:** ¿Esta orden ya existe?
   - **Sí:** Carga automáticamente la tabla de productos relacionados al final de la pantalla.
   - **No:** Oculta la sección de productos hasta que guardes la orden por primera vez.
3. **Agregas un producto:** Al crear una línea en la tabla de detalles, el sistema le pega invisiblemente el número de la orden padre.
4. **Guardas:** Al guardar el detalle, el sistema actualiza los totales y todo se mantiene sincronizado.

¡Y listo! Con estos pasos logras que dos tablas funcionen como una sola pantalla integrada.
