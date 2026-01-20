# ⚡ JavaScript Functions: `getValue` and `getObject`

This document describes the utility functions `getValue` and `getObject`, used in the project to perform asynchronous requests to the server. These functions simplify the use of `fetch` and handle parameter serialization, timeouts, and retries.

## `getValue(url, params, callback)`

Performs a `POST` HTTP request to a specified URL and returns the response as plain text.

### Parameters

- **`url`** (string): The URL to request. Internally processed with `fixUrl`, so it can be a relative path.
- **`params`** (object, optional): An object with parameters sent in the request body (encoded as `application/x-www-form-urlencoded`).
  - In addition to data, this object accepts special configurations:
    - `timeout` (number): Max wait time in milliseconds (default: 12000).
    - `retryAttempts` (number): Number of attempts in case of failure (default: 1).
    - `retryDelay` (number): Delay between attempts in milliseconds (default: 1000).
- **`callback`** (function, optional): A function executed upon request completion. Receives two arguments: `(response, error)`.

### Return

- If `callback` is provided, the function returns nothing and result is handled in the callback.
- If **no** `callback` is provided, returns a `Promise` that resolves with the response text or rejects with an error.

### Reference Examples

!!! tip "Modernize your code"

    Although callbacks are supported, we strongly recommend using `async/await` syntax for cleaner and more readable code, avoiding "callback hell".

**Basic Usage with Promises (Async/Await):**

```javascript
try {
  const responseText = await getValue("controller/method", { id: 123 });
  console.log("Server response:", responseText);
} catch (error) {
  console.error("An error occurred:", error);
}
```

**Usage with Callback:**

```javascript
getValue("controller/method", { user: "juan" }, function (response, error) {
  if (error) {
    console.error("Error:", error);
  } else {
    console.log("Response:", response);
  }
});
```

**Timeout and Retry Configuration:**

```javascript
const params = {
  data: "value",
  timeout: 5000, // Wait max 5 seconds
  retryAttempts: 3, // Retry up to 3 times if it fails
};

try {
  const res = await getValue("api/data", params);
} catch (e) {
  console.error("Failed after 3 attempts");
}
```

---

## `getObject(purl, pparameters, callbackfunction)`

A wrapper function for `getValue`. Performs the request and automatically tries to parse the response as a JSON object.

### Parameters

- **`purl`** (string): The URL to request.
- **`pparameters`** (object): Object with parameters to send.
- **`callbackfunction`** (function, optional): Callback function receiving `(object, error)`.
  - `object`: The parsed JSON result if successful (or `null` if error).
  - `error`: Error object if request failed or JSON is invalid (or `null` if successful).

### Return

- If `callbackfunction` is used, returns nothing explicit.
- If **no** `callbackfunction` is used, returns a `Promise` resolving with the parsed JavaScript object or rejects with error.

### Reference Examples

**Basic usage to get JSON data:**

```javascript
// Suppose server returns: {"name": "Ragnos", "version": 1.0}
try {
  const data = await getObject("api/config", { module: "admin" });
  console.log("System Name:", data.name);
} catch (error) {
  console.error("Error getting config:", error);
}
```

**Usage with Callback:**

```javascript
getObject("customers/search", { q: "Company X" }, function (customers, error) {
  if (error) {
    alert("Search error");
    return;
  }

  // 'customers' is already a JS array or object
  customers.forEach((c) => console.log(c.name));
});
```

---

## `RagnosSearch` and Related Classes

The `RagnosSearch` class and its helper methods provide a standardized interface for performing searches on inputs, integrating GUI (buttons) and result handling.

### `RagnosSearch.setupSimpleSearch(element, route, params, callback)`

Static method to configure simple search on an existing input. Transforms input by adding search and clear buttons.

#### Parameters

- **`element`** (jQuery Selector | DOM Element): The input to enable search on.
- **`route`** (string): Server URL (Controller/Method) to process search.
- **`params`** (object): Additional configuration.
  - `canSetToNull` (boolean): Defines if "X" button is shown to clear field (Default: `true`).
- **`callback`** (function): Function to execute after successful search. Receives jQuery object of input as argument `e`.

#### Return

Returns nothing. Modifies input DOM.

#### Usage Example

```javascript
RagnosSearch.setupSimpleSearch(
  $("#myInput"),
  "admin/users/search",
  {},
  function (e) {
    // Result data is attached to jQuery object in 'searchdata'
    let result = e.data("searchdata");

    if (result) {
      console.log("Selected ID:", result.id);
      console.log("Name:", result.name);
      // Assign value to visible input
      e.val(result.name);
    }
  },
);
```

---

### `$(selector).RagnosSearch(params)`

jQuery plugin instantiating `RagnosSearch` class. Designed for complex searches, typically linked to a standard system controller (`RagnosController`) implementing `searchByAjax` and supporting structured filters.

#### Parameters (`params`)

Configuration object with:

- **`controller`** (string): Name of controller managing search (e.g. `'users'`, `'products'`). System searches `controller/searchByAjax`.
- **`filter`** (string): Base64 string containing JSON array of filters.
  - Structure: `Base64( JSON_String([ {field, op, value}, ... ]) )`.
- **`callback`** (function): Function to execute on result selection.
- **`canSetToNull`** (boolean): (Optional) Allows clearing the field.

#### Usage Example

```javascript
$("#advancedSearchInput").RagnosSearch({
  controller: "users", // Searches in: users/searchByAjax

  // Filter: Active users (usu_activo = 'S') and group 2
  filter: btoa(
    JSON.stringify([
      { field: "usu_activo", op: "=", value: "S" },
      { field: "usu_grupo", op: "=", value: 2 },
    ]),
  ),

  callback: function (e) {
    let data = e.data("searchdata");
    console.log("Received data:", data);

    if (data && data.id) {
      // Custom logic
    }
  },
});
```

---

## System Hooks and Functions in `custom.js`

The `custom.js` file is the designated place for application-specific logic, including "hooks" (handlers) the system automatically invokes based on naming conventions. This allows extending search and table functionality without modifying core.

### Hook Naming Convention

System automatically detects global functions with specific naming patterns to execute actions after events.

#### 1. Search Hooks (`_FieldNameOnSearch`)

Executed automatically after a `RagnosSearch` control completes a selection.

- **Pattern:** `_{input_id}OnSearch`
- **Parameter:** Receives control (input) jQuery object.
- **Usage:** Ideal for filling other form fields based on search result.

**System Example (`_productCodeOnSearch`):**
When a product is selected in `productCode` input, this function finds `priceEach` input in order detail and assigns MSRP.

```javascript
// Activated when selecting something in <input id="productCode" ...>
function _productCodeOnSearch(control) {
  // 'control' is product code input
  // Access data returned by search with .data("searchdata")
  let data = control.data("searchdata");

  // Update another field (Unit Price) with product MSRP
  $('#orderdetail input[name="priceEach"]').val(data.MSRP);
}
```

#### 2. Table Change Hooks (`_TableNameOnChange`)

Executed when a managed table changes (e.g. adding/editing rows in detail table).

- **Pattern:** `_{table_id}OnChange`
- **Parameter:** Receives table object or change context.
- **Usage:** Total recalculations, cross-validations.

**System Example (`_OrderDetailsOnChange`):**
Every time something changes in `OrderDetails` table, complete order total is recalculated by calling server.

```javascript
// Activated when modifying table <table id="OrderDetails" ...>
function _OrderDetailsOnChange(table) {
  // Get current order ID
  let order = $("input[name='orderNumber']").val();

  // Call server to recalculate total
  getObject("store/orders/calculatetotal", { order: order }, function (data) {
    // Update visual Total field
    $('input[name="total"]').val(data.total);
  });
}
```

### Custom Utility Functions

Normal functions can also be defined for explicit callbacks in search configs.

**Example (`officeSearchTest`):**
Simple function designed to be passed as `callback` parameter in `RagnosSearch`.

```javascript
function officeSearchTest(e) {
  let data = e.data("searchdata");
  console.log("Office search data:", data);
}

// Usage:
// $('#office').RagnosSearch({ ..., callback: officeSearchTest });
```
