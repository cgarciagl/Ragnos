# ⚡ JavaScript Functions and `Ragnos` Namespace

This document describes the utility functions `getValue` and `getObject`, as well as the unified `window.Ragnos` namespace architecture used in the project for asynchronous requests, table manipulation, and UI components.

!!! info "Global `Ragnos.*` Namespace"

    All client utility functions and JavaScript components are cleanly organized under the unified `window.Ragnos` namespace:

    - `Ragnos.Http`: AJAX requests and redirects (`getValue`, `getObject`, `postFormData`, `fixUrl`, etc.).
    - `Ragnos.UI`: Toast notifications, Modals, and visual feedback (`showToast`, `showModal`, `cierraModal`, `mostrarCargando`, etc.).
    - `Ragnos.Table`: DataTables and table export/totals (`destroyDataTable`, `ponTablaPaginada`, `exportToExcel`, etc.).
    - `Ragnos.DOM`: DOM manipulation and helpers (`onReady`, `getElement`, `debounce`, `moneyFormat`, etc.).
    - `Ragnos.Search`, `Ragnos.Editor`, `Ragnos.Utils`, `Ragnos.Stack`: Core `ragnos.js` components.

    For 100% backward compatibility, all functions and classes remain available as direct global aliases (`getValue`, `showToast`, `RagnosSearch`, etc.).

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

Static method to configure simple search on an existing input. Transforms input by adding or reusing search and clear buttons within an `.input-group`, ensuring idempotency via the `Ragnosffied` class marker.

#### Parameters

- **`element`** (string | DOM Element): CSS selector or input DOM element where search will be enabled.
- **`route`** (string): Server URL (Controller/Method) to process search.
- **`params`** (object): Additional configuration.
  - `canSetToNull` (boolean): Defines if "X" button is shown to clear field (Default: `true`).
- **`callback`** (function, optional): Function executed after a search or field clear. Receives input DOM element as argument `e`.

#### Return

- Returns configured `HTMLInputElement` (or `null` if element is invalid). Returns early if control was already marked with `Ragnosffied`.

#### Key Features

- **Idempotency (`Ragnosffied`)**: Prevents duplicate event listeners and buttons if invoked multiple times on the same input.
- **Search via Button and `Enter` Key**: Clicking the search icon or pressing `Enter` executes search with current value (`input.value`) and prevents unintended form submissions.
- **State & Hidden Fields Synchronization**: Clearing input or clicking "X" button resets `input.ragnosSearchData = null` and wipes associated hidden input fields.
- **Convention Hook Support**: Automatically looks up and triggers global `_{id}OnSearch(control)` or `_{name}OnSearch(control)` functions in `custom.js` when search or clear actions complete.

#### Usage Example

```javascript
RagnosSearch.setupSimpleSearch(
  document.querySelector("#myInput"),
  "admin/users/search",
  {},
  function (e) {
    // Result data is attached to the DOM element
    const result = e.ragnosSearchData;

    if (result) {
      console.log("Selected ID:", result.id);
      console.log("Name:", result.name);
      // Assign value to visible input
      e.value = result.name;
    }
  },
);
```

---

### `new RagnosSearch(element, params)`

Native class for complex searches, typically linked to a standard system controller (`RagnosController`) implementing `searchByAjax` and supporting structured filters.

#### Parameters (`params`)

Configuration object with:

- **`controller`** (string): Name of controller managing search (e.g. `'users'`, `'products'`). System searches `controller/searchByAjax`.
- **`filter`** (string): Base64 string containing JSON array of filters.
  - Structure: `Base64( JSON_String([ {field, op, value}, ... ]) )`.
- **`callback`** (function): Function to execute on result selection.
- **`canSetToNull`** (boolean): (Optional) Allows clearing the field.

#### Usage Example

```javascript
new RagnosSearch(document.querySelector("#advancedSearchInput"), {
  controller: "users", // Searches in: users/searchByAjax

  // Filter: Active users (usu_activo = 'S') and group 2
  filter: btoa(
    JSON.stringify([
      { field: "usu_activo", op: "=", value: "S" },
      { field: "usu_grupo", op: "=", value: 2 },
    ]),
  ),

  callback: function (e) {
    const data = e.ragnosSearchData;
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
- **Parameter:** Receives the control's DOM element (input).
- **Usage:** Ideal for filling other form fields based on search result.

**System Example (`_productCodeOnSearch`):**
When a product is selected in `productCode` input, this function finds `priceEach` input in order detail and assigns MSRP.

```javascript
// Activated when selecting something in <input id="productCode" ...>
function _productCodeOnSearch(control) {
  // 'control' is product code input
  // Access data returned by the search
  const data = control.ragnosSearchData;

  // Update another field (Unit Price) with product MSRP
  document.querySelector('#orderdetail input[name="priceEach"]').value =
    data.MSRP;
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
  const order = document.querySelector("input[name='orderNumber']").value;

  // Call server to recalculate total
  getObject("store/orders/calculatetotal", { order: order }, function (data) {
    // Update visual Total field
    document.querySelector('input[name="total"]').value = data.total;
  });
}
```

### Custom Utility Functions

Normal functions can also be defined for explicit callbacks in search configs.

**Example (`officeSearchTest`):**
Simple function designed to be passed as `callback` parameter in `RagnosSearch`.

```javascript
function officeSearchTest(e) {
  const data = e.ragnosSearchData;
  console.log("Office search data:", data);
}

// Usage:
// new RagnosSearch('#office', { ..., callback: officeSearchTest });
```

---

## `RagnosUtils.showControllerTableIn(selector, controller, master)`

Asynchronously loads a table generated by a controller into a specific DOM element. This function is essential when handling "Detail" relationships or when you want to dynamically embed a table view.

### Parameters

- **`selector`** (string | DOM Element): CSS selector (e.g. `'#my_detail_table'`) or element where the HTML content will be injected.
- **`controller`** (string): Controller name (or URL path) that will respond to the request. Internally calls the `tableByAjax` method of the controller.
- **`master`** (string, optional): The ID of the master record. If provided, it is associated with the global security object `Ragnos_csrf` to filter results by this parent ID.

### Usage Example

```javascript
// Load detail table for a specific order
const orderId = "10123";
RagnosUtils.showControllerTableIn(
  "#detail-container",
  "sales/details",
  orderId,
);
```

---

## `RagnosUtils.showControllerReportIn(selector, controller)`

Similar to `showControllerTableIn`, but specifically designed to load report views generated by the controller.

### Parameters

- **`selector`** (string | DOM Element): CSS selector or element where the report will be injected.
- **`controller`** (string): Controller name. Internally calls the `reportByAjax` method of the controller.

### Usage Example

```javascript
// Load a statistical summary in a side div
RagnosUtils.showControllerReportIn("#sidebar-report", "statistics/sales_chart");
```
