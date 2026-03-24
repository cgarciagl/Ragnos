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

| Header          | Valor                  | Descripción                                                       |
| :-------------- | :--------------------- | :---------------------------------------------------------------- |
| `Accept`        | `application/json`     | **Obligatorio.** Le dice a Ragnos que no quieres HTML, sino JSON. |
| `Authorization` | `Bearer TU_TOKEN_AQUI` | **Obligatorio.** Tu token de seguridad.                           |
| `Content-Type`  | `application/json`     | Necesario cuando envías datos (POST/PUT).                         |

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

#### Parámetros de Paginación y Búsqueda

Ragnos utiliza internamente el formato de parámetros de **DataTables** para manejar la grilla desde la API. Al hacer peticiones `GET` para listar registros, puedes enviar los siguientes parámetros en la *Query String* de la URL:

- `start`: El índice de desplazamiento desde donde traer registros (Offset). Equivale a `(página - 1) * limite`. (ej. `0`)
- `length`: El número de registros a traer (Límite máximo por página). El valor por defecto es 10.
- `search[value]`: Cadena de texto para buscar coincidencias rápidas en toda la tabla.

Ejemplo de uso:
`GET /tienda/productos?start=0&length=15&search[value]=Laptop`

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
    "amount": 500.00
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

**Ejemplo: Error de Validación (400 Bad Request)**

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

**Ejemplo: Token Inválido (401 Unauthorized)**

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
