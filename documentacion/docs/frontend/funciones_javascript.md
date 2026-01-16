# Funciones JavaScript: `getValue` y `getObject`

Este documento describe las funciones utilitarias `getValue` y `getObject`, utilizadas en el proyecto para realizar peticiones asíncronas al servidor. Estas funciones simplifican el uso de `fetch` y manejan la serialización de parámetros, tiempos de espera y reintentos.

## `getValue(url, params, callback)`

Realiza una petición HTTP `POST` a una URL especificada y devuelve la respuesta como texto plano.

### Parámetros

- **`url`** (string): La URL a la que se realizará la petición. Se procesa internamente con `fixUrl`, por lo que puede ser una ruta relativa.
- **`params`** (object, opcional): Un objeto con los parámetros que se enviarán en el cuerpo de la petición (codificados como `application/x-www-form-urlencoded`).
  - Además de los datos a enviar, este objeto acepta configuraciones especiales:
    - `timeout` (number): Tiempo máximo de espera en milisegundos (por defecto: 12000).
    - `retryAttempts` (number): Número de intentos en caso de fallo (por defecto: 1).
    - `retryDelay` (number): Retardo entre intentos en milisegundos (por defecto: 1000).
- **`callback`** (function, opcional): Una función que se ejecutará al completar la petición. Recibe dos argumentos: `(response, error)`.

### Retorno

- Si se proporciona un `callback`, la función no retorna nada y el resultado se maneja en el callback.
- Si **no** se proporciona un `callback`, devuelve una `Promise` que resuelve con el texto de la respuesta o rechaza con un error.

### Ejemplos de uso

**Uso básico con Promesas (Async/Await):**

```javascript
try {
  const respuestaTexto = await getValue("controller/metodo", { id: 123 });
  console.log("Respuesta del servidor:", respuestaTexto);
} catch (error) {
  console.error("Hubo un error:", error);
}
```

**Uso con Callback:**

```javascript
getValue("controller/metodo", { usuario: "juan" }, function (response, error) {
  if (error) {
    console.error("Error:", error);
  } else {
    console.log("Respuesta:", response);
  }
});
```

**Configuración de timeout y reintentos:**

```javascript
const params = {
  dato: "valor",
  timeout: 5000, // Esperar máximo 5 segundos
  retryAttempts: 3, // Reintentar hasta 3 veces si falla
};

try {
  const res = await getValue("api/data", params);
} catch (e) {
  console.error("Falló tras 3 intentos");
}
```

---

## `getObject(purl, pparameters, callbackfunction)`

Es una función envolvente (wrapper) de `getValue`. Realiza la petición y automáticamente intenta parsear la respuesta como un objeto JSON.

### Parámetros

- **`purl`** (string): La URL a la que se realizará la petición.
- **`pparameters`** (object): Objeto con los parámetros a enviar.
- **`callbackfunction`** (function, opcional): Función de callback que recibe `(objeto, error)`.
  - `objeto`: El resultado parseado como JSON si todo salió bien (o `null` si hubo error).
  - `error`: Objeto de error si la petición falló o el JSON no es válido (o `null` si todo salió bien).

### Retorno

- Si se usa `callbackfunction`, no retorna nada explícito.
- Si **no** se usa `callbackfunction`, devuelve una `Promise` que resuelve con el objeto JavaScript parseado o rechaza con el error.

### Ejemplos de uso

**Uso básico para obtener datos JSON:**

```javascript
// Supongamos que el servidor devuelve: {"nombre": "Ragnos", "version": 1.0}
try {
  const data = await getObject("api/config", { modulo: "admin" });
  console.log("Nombre del sistema:", data.nombre);
} catch (error) {
  console.error("Error obteniendo configuración:", error);
}
```

**Uso con Callback:**

```javascript
getObject("clientes/buscar", { q: "Empresa X" }, function (clientes, error) {
  if (error) {
    alert("Error en la búsqueda");
    return;
  }

  // 'clientes' ya es un array u objeto JS
  clientes.forEach((c) => console.log(c.nombre));
});
```

---

## `RagnosSearch` y Clases relacionadas

La clase `RagnosSearch` y sus métodos auxiliares proporcionan una interfaz estandarizada para realizar búsquedas en inputs, integrando interfaz gráfica (botones) y manejo de resultados.

### `RagnosSearch.setupSimpleSearch(elemento, ruta, params, callback)`

Método estático para configurar una búsqueda sencilla en un input existente. Transforma el input agregando botones de búsqueda y limpieza.

#### Parámetros

- **`elemento`** (jQuery Selector | DOM Element): El input donde se habilitará la búsqueda.
- **`ruta`** (string): La URL del servidor (Controlador/Método) que procesará la búsqueda.
- **`params`** (object): Configuración adicional.
  - `canSetToNull` (boolean): Define si se muestra el botón "X" para limpiar el campo (Default: `true`).
- **`callback`** (function): Función a ejecutar tras una búsqueda exitosa. Recibe el objeto jQuery del input como argumento `e`.

#### Retorno

No retorna valor. Modifica el DOM del input.

#### Ejemplo de uso

```javascript
RagnosSearch.setupSimpleSearch(
  $("#miInput"),
  "admin/usuarios/buscar",
  {},
  function (e) {
    // Los datos del resultado se adjuntan al objeto jQuery en 'searchdata'
    let resultado = e.data("searchdata");

    if (resultado) {
      console.log("ID seleccionado:", resultado.id);
      console.log("Nombre:", resultado.nombre);
      // Asignar valor al input visible
      e.val(resultado.nombre);
    }
  }
);
```

---

### `$(selector).RagnosSearch(params)`

Plugin de jQuery que instancia la clase `RagnosSearch`. Está diseñado para búsquedas más complejas, típicamente vinculadas a un controlador estándar del sistema (`RagnosController`) que implementa `searchByAjax` y soporta filtros estructurados.

#### Parámetros (`params`)

Objeto de configuración con:

- **`controller`** (string): Nombre del controlador que gestiona la búsqueda (ej. `'usuarios'`, `'productos'`). El sistema buscará en `controller/searchByAjax`.
- **`filter`** (string): Cadena en Base64 que contiene un array JSON de filtros.
  - Estructura: `Base64( JSON_String([ {field, op, value}, ... ]) )`.
- **`callback`** (function): Función a ejecutar al seleccionar un resultado.
- **`canSetToNull`** (boolean): (Opcional) Permite limpiar el campo.

#### Ejemplo de uso

```javascript
$("#inputBusquedaAvanzada").RagnosSearch({
  controller: "usuarios", // Busca en: usuarios/searchByAjax

  // Filtro: Usuarios activos (usu_activo = 'S') y del grupo 2
  filter: btoa(
    JSON.stringify([
      { field: "usu_activo", op: "=", value: "S" },
      { field: "usu_grupo", op: "=", value: 2 },
    ])
  ),

  callback: function (e) {
    let datos = e.data("searchdata");
    console.log("Datos recibidos:", datos);

    if (datos && datos.id) {
      // Lógica personalizada
    }
  },
});
```

---

## Hooks de Sistema y Funciones en `custom.js`

El archivo `custom.js` es el lugar designado para lógica específica de la aplicación, incluyendo "hooks" (ganchos) que el sistema invoca automáticamente basándose en convenciones de nombres. Esto permite extender la funcionalidad de búsquedas y tablas sin modificar el núcleo.

### Convención de Nombres para Hooks

El sistema detecta automáticamente funciones globales con patrones específicos de nombre para ejecutar acciones tras ciertos eventos.

#### 1. Hooks de Búsqueda (`_NombreCampoOnSearch`)

Se ejecutan automáticamente después de que un control `RagnosSearch` completa una selección.

- **Patrón:** `_{id_del_input}OnSearch`
- **Parámetro:** Recibe el objeto jQuery del control (input).
- **Uso:** Ideal para rellenar otros campos del formulario basándose en el resultado de la búsqueda.

**Ejemplo del sistema (`_productCodeOnSearch`):**
Cuando se selecciona un producto en el input `productCode`, esta función busca el input `priceEach` en el detalle de la orden y le asigna el precio sugerido (MSRP).

```javascript
// Se activa al seleccionar algo en <input id="productCode" ...>
function _productCodeOnSearch(control) {
  // 'control' es el input del código de producto
  // Accedemos a los datos devueltos por la búsqueda con .data("searchdata")
  let datos = control.data("searchdata");

  // Actualizamos otro campo (Precio Unitario) con el valor MSRP del producto
  $('#detalleorden input[name="priceEach"]').val(datos.MSRP);
}
```

#### 2. Hooks de Cambio en Tabla (`_NombreTablaOnChange`)

Se ejecutan cuando ocurre un cambio en una tabla gestionada (por ejemplo, al agregar o editar filas en una tabla detalle).

- **Patrón:** `_{id_de_la_tabla}OnChange`
- **Parámetro:** Recibe el objeto tabla o el contexto del cambio.
- **Uso:** Recálculos de totales, validaciones cruzadas.

**Ejemplo del sistema (`_OrdenesdetallesOnChange`):**
Cada vez que cambia algo en la tabla de detalles de órdenes (`Ordenesdetalles`), se recalcula el total de la orden completa llamando al servidor.

```javascript
// Se activa al modificar la tabla <table id="Ordenesdetalles" ...>
function _OrdenesdetallesOnChange(tabla) {
  // Obtenemos el ID de la orden actual
  let orden = $("input[name='orderNumber']").val();

  // Llamamos al servidor para recalcular el total
  getObject("tienda/ordenes/calculatotal", { orden: orden }, function (data) {
    // Actualizamos el campo visual de Total
    $('input[name="total"]').val(data.total);
  });
}
```

### Funciones Utilitarias Personalizadas

También se pueden definir funciones normales para ser usadas como callbacks explícitos en configuraciones de búsqueda.

**Ejemplo (`pruebaBusquedaOffice`):**
Una función simple diseñada para ser pasada como parámetro `callback` en `RagnosSearch`.

```javascript
function pruebaBusquedaOffice(e) {
  let datos = e.data("searchdata");
  console.log("Los datos de la busqueda por oficina", datos);
}

// Uso:
// $('#oficina').RagnosSearch({ ..., callback: pruebaBusquedaOffice });
```
