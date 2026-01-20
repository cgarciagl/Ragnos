# Usage Guide: Ragnos API Mode

Starting from v1.1, **Ragnos Framework** supports hybrid controllers. This means same controller serving admin panel (HTML) can work as **RESTful API** (JSON) without extra code.

---

## 📋 Prerequisites

1. **Access Token:** Must have valid `api_token` assigned to user in database.
2. **Endpoint:** URL is same as used in browser (e.g. `https://your-site.com/store/products`).

---

## Authenticate with API Mode and get token

To use API Mode, first authenticate and obtain token. Send POST request to login endpoint with credentials.
**Request:**

```http
POST /admin/login
Headers:
  Content-Type: application/json
  Accept: application/json
Body (JSON):
{
    "usuario": "admin",
    "pword": "your_password"
}
```

**Response (200 OK):**

```json
{
  "status": "success",
  "message": "Login successful",
  "token": "4984656be2ff893362b3b023d0b55df74494fb87552916031143e6431e8d9a7c",
  "user_id": "1"
}
```

You get a token to use in headers for future API requests.

## 🔐 Authentication and Headers

To activate "API Mode", client **must** send following HTTP headers. If not sent, Ragnos responds with HTML.

| Header          | Value               | Description                                          |
| :-------------- | :------------------ | :--------------------------------------------------- |
| `Accept`        | `application/json`  | **Mandatory.** Tells Ragnos you want JSON, not HTML. |
| `Authorization` | `Bearer YOUR_TOKEN` | **Mandatory.** Your security token.                  |
| `Content-Type`  | `application/json`  | Necessary when sending data (POST/PUT).              |

---

## 📡 Endpoints and Operations

Assume controller is `Store/Products`.

### 1. List Records (GET)

Gets list of data applying grid filters.

**Request:**

```http
GET /store/products
Headers:
  Accept: application/json
  Authorization: Bearer xyz123
```

**Response (200 OK):**

```json
{
  "status": 200,
  "data": [
    { "id": 1, "name": "Laptop", "price": 1500 },
    { "id": 2, "name": "Mouse", "price": 20 }
  ],
  "count": 2
}
```

---

### 2. Create Record (POST)

**Request:**

```http
POST /store/products/save
Headers:
  Content-Type: application/json
  Accept: application/json
  Authorization: Bearer xyz123

Body (JSON):
{
    "name": "Mechanical Keyboard",
    "price": 85.50,
    "stock": 10
}
```

**Response (201 Created):**

```json
{
  "status": 201,
  "message": "Record created successfully.",
  "data": {
    "id": 15
  }
}
```

---

### 3. Update Record (POST/PUT)

In Ragnos, update handled in same `save` endpoint. Difference is **must include ID** (primary key).

**Request:**

```http
POST /store/products/save
Headers: ...

Body (JSON):
{
    "id": 15,
    "price": 90.00
}
```

**Response (200 OK):**

```json
{
  "status": 200,
  "message": "Record updated successfully.",
  "data": { "id": 15 }
}
```

---

### 4. Delete Record (POST/DELETE)

Can send ID in URL or JSON body.

**Option A (URL):** `POST /store/products/delete/15`

**Option B (JSON Body):**

**Request:**

```http
POST /store/products/delete
Headers: ...

Body (JSON):
{
    "id": 15
}
```

**Response (200 OK):**

```json
{
  "status": 200,
  "message": "Record deleted successfully"
}
```

---

## ⚠️ Error Handling

If something fails (validation or server), Ragnos returns appropriate HTTP status and error JSON.

**Example: Validation Error (400 Bad Request)**

```json
{
  "status": 400,
  "error": 400,
  "messages": {
    "price": "Price field is required.",
    "stock": "Stock field must be numeric."
  }
}
```

**Example: Invalid Token (401 Unauthorized)**

```json
{
  "status": 401,
  "error": "Token invalid or expired"
}
```

---

## 💻 Client Examples

### JavaScript (Fetch)

```javascript
const token = "YOUR_API_TOKEN";

// Example: Save product
fetch("https://yoursite.com/store/products/save", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
    Authorization: `Bearer ${token}`,
  },
  body: JSON.stringify({
    name: "4K Monitor",
    price: 300,
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data))
  .catch((error) => console.error("Error:", error));
```

### cURL (Terminal)

```bash
curl -X GET https://yoursite.com/store/products   -H "Accept: application/json"   -H "Authorization: Bearer YOUR_API_TOKEN"
```
