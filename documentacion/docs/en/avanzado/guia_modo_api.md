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

| Header             | Value               | Description                                          |
| :----------------- | :------------------ | :--------------------------------------------------- |
| `Accept`           | `application/json`  | **Mandatory.** Tells Ragnos you want JSON, not HTML. |
| `Authorization`    | `Bearer YOUR_TOKEN` | **Mandatory.** Your security token.                  |
| `Content-Type`     | `application/json`  | Necessary when sending data (POST/PUT).              |
| `X-Requested-With` | `XMLHttpRequest`    | Identifies the request as AJAX/Fetch.                |

---

## 🛠️ Reference Implementation (Fetch API)

To make it easier to consume the API, we recommend using a "wrapper" around fetch, as seen in the `apitest/app.js` example:

```javascript
async function apiCall(endpoint, options = {}) {
  const { method = "GET", body = null, token = null, params = {} } = options;
  let url = `${API_BASE}/${endpoint}`;

  // Query Parameters
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
  "count": 2,
  "total": 122
}
```

#### Pagination, Search, and Sorting Parameters

Ragnos internally uses the **DataTables** parameter format to handle grid data querying from the API. This allows for smooth integration with frontend components. When making `GET` requests to list records, you can pass the following parameters in the URL Query String:

- **Pagination:**
  - `start`: The starting offset index to fetch records from (e.g., `0` for page 1, `10` for page 2 if the limit is 10).
  - `length`: The number of records to fetch per page (Limit). The default is 10.
- **Search:**
  - `search[value]`: A text string to search for matches across all enabled fields in the controller.
- **Sorting:**
  - `order[0][name]`: (Recommended) The name of the database column to sort by (e.g., `date`, `name`, `id`).
  - `order[0][dir]`: The sorting direction, either `asc` (ascending) or `desc` (descending).

##### JavaScript Implementation Example (Fetch)

Inspired by the `apitest` example, here's how you can dynamically build the parameters:

```javascript
const ITEMS_PER_PAGE = 10;
const currentPage = 1;
const searchTerm = 'Laptop';
const sortField = 'price';
const sortDir = 'desc';

const params = {
    start: (currentPage - 1) * ITEMS_PER_PAGE,
    length: ITEMS_PER_PAGE,
    'search[value]': searchTerm,
    'order[0][name]': sortField,
    'order[0][dir]': sortDir
};

// Convert object to Query String
const queryString = new URLSearchParams(params).toString();
const url = `/store/products?${queryString}`;

const response = await fetch(url, {
    headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
        'X-Requested-With': 'XMLHttpRequest'
    }
});
const result = await response.json();
console.log('Data:', result.data); // Records
console.log('Total:', result.total); // Total filtered for pagination
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

#### Relational Fields (SearchFields)

If your controller uses `addSearch()` for related fields (e.g., `customerNumber` which opens a Customer lookup), in **API Mode** you can simply send the raw ID of the relationship in the corresponding field.

Ragnos will automatically process the value without the internal prefixes used by the web interface.

**Payment Example with Relation:**

```json
{
  "customerNumber": 103,
  "checkNumber": "ABC-123",
  "paymentDate": "2024-03-24",
  "amount": 500.0
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

Example: Validation Error (400 Bad Request)

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

Example: Invalid Token (401 Unauthorized)

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
