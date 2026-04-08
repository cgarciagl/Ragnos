# Guía de Uso: Ragnos API Mode

A partir de la versión v1.1, **Ragnos Framework** soporta controladores híbridos. Esto significa que el mismo controlador que sirve tu panel administrativo (HTML) puede funcionar como una **API RESTful** (JSON) sin escribir código adicional.

---

## 📋 Requisitos Previos

1. **Token de Acceso:** Debes tener un `api_token` válido asignado a tu usuario en la base de datos.
2. **Endpoint:** La URL es la misma que usas en el navegador (ej. `https://tu-sitio.com/tienda/productos`).

---

## Autenticar con el Modo API y obtener el token

Para usar el Modo API, primero debes autenticarte y obtener un token. Puedes hacerlo enviando una solicitud POST al endpoint de login con tus credenciales.
**Request:**

```http
POST /admin/login
Headers:
  Content-Type: application/json
  Accept: application/json
Body (JSON):
{
    "usuario": "admin",
    "pword": "tu_contraseña"
}
```

**Respuesta (200 OK):**

```json
{
  "status": "success",
  "message": "Login successful",
  "token": "4984656be2ff893362b3b023d0b55df74494fb87552916031143e6431e8d9a7c",
  "user_id": "1"
}
```

Así obtendrás un token que deberás usar en las cabeceras de tus futuras solicitudes API.

## 🔐 Autenticación y Cabeceras

Para activar el "Modo API", el cliente **debe** enviar las siguientes cabeceras HTTP. Si no se envían, Ragnos responderá con HTML (redirecciones y vistas).

| Header             | Valor                  | Descripción                                                       |
| :----------------- | :--------------------- | :---------------------------------------------------------------- |
| `Accept`           | `application/json`     | **Obligatorio.** Le dice a Ragnos que no quieres HTML, sino JSON. |
| `Authorization`    | `Bearer TU_TOKEN_AQUI` | **Obligatorio.** Tu token de seguridad.                           |
| `Content-Type`     | `application/json`     | Necesario cuando envías datos (POST/PUT).                         |
| `X-Requested-With` | `XMLHttpRequest`       | Identifica la petición como AJAX/Fetch.                           |

---

## 🛠️ Implementación de Referencia (Fetch API)

Para facilitar el consumo de la API, se recomienda usar un "wrapper" de fetch como el que se encuentra en el ejemplo `apitest/app.js`:

```javascript
async function apiCall(endpoint, options = {}) {
  const { method = "GET", body = null, token = null, params = {} } = options;
  let url = `${API_BASE}/${endpoint}`;

  // Parámetros de consulta (Query Params)
  const searchParams = new URLSearchParams();
  for (const [key, val] of Object.entries(params)) {
    if (val) searchParams.set(key, val);
  }
  if (searchParams.toString()) url += "?" + searchParams.toString();

  const headers = {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  };

  if (token) headers["Authorization"] = `Bearer ${token}`;

  const fetchOptions = { method, headers };

  if (body && (method === "POST" || method === "PUT")) {
    headers["Content-Type"] = "application/json";
    fetchOptions.body = JSON.stringify(body);
  }

  const response = await fetch(url, fetchOptions);
  return await response.json();
}
```

---

## 📡 Endpoints y Operaciones

Supongamos que tu controlador es `Tienda/Productos`.

### 1. Listar Registros (GET)

Obtiene la lista de datos aplicando los filtros de la grilla.

**Request:**

```http
GET /tienda/productos
Headers:
  Accept: application/json
  Authorization: Bearer xyz123
```

**Respuesta (200 OK):**

```json
{
  "status": 200,
  "data": [
    { "id": 1, "nombre": "Laptop", "precio": 1500 },
    { "id": 2, "nombre": "Mouse", "precio": 20 }
  ],
  "count": 2,
  "total": 122
}
```

#### Parámetros de Paginación, Búsqueda y Ordenamiento

Ragnos utiliza internamente el formato de parámetros de **DataTables** para manejar la grilla desde la API. Esto permite una integración fluida con componentes de frontend. Al hacer peticiones `GET` para listar registros, puedes enviar los siguientes parámetros en la _Query String_ de la URL:

- **Paginación:**
  - `start`: El índice de desplazamiento (Offset). Indica desde qué registro empezar (ej. `0` para la página 1, `10` para la página 2 si el límite es 10).
  - `length`: El número de registros a traer (Límite). Por defecto es 10.
- **Búsqueda:**
  - `search[value]`: Cadena de texto para realizar una búsqueda global en todos los campos habilitados del controlador.
- **Ordenamiento:**
  - `order[0][name]`: (Recomendado) El nombre de la columna por la cual deseas ordenar (ej. `fecha`, `nombre`, `id`).
  - `order[0][dir]`: La dirección del ordenamiento, ya sea `asc` (ascendente) o `desc` (descendente).

##### Ejemplo de implementación en JavaScript (Fetch)

Inspirado en el ejemplo `apitest`, así podrías construir los parámetros dinámicamente:

```javascript
const ITEMS_PER_PAGE = 10;
const currentPage = 1;
const searchTerm = "Laptop";
const sortField = "precio";
const sortDir = "desc";

const params = {
  start: (currentPage - 1) * ITEMS_PER_PAGE,
  length: ITEMS_PER_PAGE,
  "search[value]": searchTerm,
  "order[0][name]": sortField,
  "order[0][dir]": sortDir,
};

// Convertir objeto a Query String
const queryString = new URLSearchParams(params).toString();
const url = `/tienda/productos?${queryString}`;

const response = await fetch(url, {
  headers: {
    Accept: "application/json",
    Authorization: `Bearer ${token}`,
    "X-Requested-With": "XMLHttpRequest",
  },
});
const result = await response.json();
console.log("Datos:", result.data); // Registros
console.log("Total:", result.total); // Total filtrado para paginación
```

---

### 2. Crear un Registro (POST)

**Request:**

```http
POST /tienda/productos/save
Headers:
  Content-Type: application/json
  Accept: application/json
  Authorization: Bearer xyz123

Body (JSON):
{
    "nombre": "Teclado Mecánico",
    "precio": 85.50,
    "stock": 10
}
```

**Respuesta (201 Created):**

```json
{
  "status": 201,
  "message": "Registro creado exitosamente.",
  "data": {
    "id": 15
  }
}
```

---

### 3. Actualizar un Registro (POST/PUT)

En Ragnos, la actualización se maneja en el mismo endpoint `save`. La diferencia es que **debes incluir el ID** (clave primaria).

**Request:**

```http
POST /tienda/productos/save
Headers: ... (mismos de arriba)

Body (JSON):
{
    "id": 15,
    "precio": 90.00
}
```

**Respuesta (200 OK):**

```json
{
  "status": 200,
  "message": "Registro actualizado exitosamente.",
  "data": { "id": 15 }
}
```

#### Campos Relacionales (SearchFields)

Si tu controlador utiliza `addSearch()` para campos relacionados (por ejemplo, `customerNumber` que abre un buscador de Clientes), en el **Modo API** puedes enviar simplemente el ID crudo de la relación en el campo correspondiente.

Ragnos procesará automáticamente el valor sin necesidad de los prefijos internos que utiliza la interfaz web.

**Ejemplo de Pago con Relación:**

```json
{
  "customerNumber": 103,
  "checkNumber": "ABC-123",
  "paymentDate": "2024-03-24",
  "amount": 500.0
}
```

---

### 4. Eliminar un Registro (POST/DELETE)

Puedes enviar el ID en la URL o en el cuerpo del JSON.

**Opción A (URL):** `POST /tienda/productos/delete/15`

**Opción B (JSON Body):**

**Request:**

```http
POST /tienda/productos/delete
Headers: ...

Body (JSON):
{
    "id": 15
}
```

**Respuesta (200 OK):**

```json
{
  "status": 200,
  "message": "Registro eliminado correctamente"
}
```

---

## ⚠️ Manejo de Errores

Si algo sale mal (validación o servidor), Ragnos devolverá un código de estado HTTP apropiado y un JSON de error.

Ejemplo: Error de Validación (400 Bad Request)

```json
{
  "status": 400,
  "error": 400,
  "messages": {
    "precio": "El campo Precio es obligatorio.",
    "stock": "El campo Stock debe ser numérico."
  }
}
```

Ejemplo: Token Inválido (401 Unauthorized)

```json
{
  "status": 401,
  "error": "Token inválido o expirado"
}
```

---

## 💻 Ejemplos de Cliente

### JavaScript (Fetch)

```javascript
const token = "TU_API_TOKEN";

// Ejemplo: Guardar producto
fetch("https://tusitio.com/tienda/productos/save", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
    Authorization: `Bearer ${token}`,
  },
  body: JSON.stringify({
    nombre: "Monitor 4K",
    precio: 300,
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data))
  .catch((error) => console.error("Error:", error));
```

### cURL (Terminal)

```bash
curl -X GET https://tusitio.com/tienda/productos   -H "Accept: application/json"   -H "Authorization: Bearer TU_API_TOKEN"
```
