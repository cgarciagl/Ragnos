# 📑 Master-Detail Relationship in Ragnos

This guide explains how to create a screen where you have a main record (like a **Purchase Order**) and a list of related items (the **Details** or products of that order).

## Relationship Architecture

```mermaid
graph TD
    User["User"] -->|Opens Screen| Master["Master Controller<br/>(Ordenes)"]
    Master -->|Renders| View["Main View"]
    View -->|Contains| Form["Master Form"]

    subgraph Linkage ["Linkage Logic"]
        direction TB
        Form -->|"Order ID"| Logic{"Order Exists?"}
        Logic -- NO --> Info("Only shows Form")
        Logic -- "YES (ID=100)" --> LoadDetails["Load Detail Controller"]
    end

    LoadDetails -->|"Injects ID=100 as $this->master"| Detail["Detail Controller<br/>(Ordenesdetalles)"]

    subgraph Detail ["Detail Zone"]
        direction TB
        Detail -->|Apply Filter| Filter["WHERE orderNumber = 100"]
        Filter -->|Shows| Grid["Product Grid"]
        Grid -->|New Record| NewLine["Detail Form"]
        NewLine -->|Hidden Field| Hidden["orderNumber = 100"]
    end

    %% Professional Styles
    classDef mainNode fill:#f9f,stroke:#333,stroke-width:2px;
    classDef detailNode fill:#bbf,stroke:#333,stroke-width:2px;
    classDef logicNode fill:#fff9c4,stroke:#fbc02d,stroke-width:2px;

    style Master fill:#e1bee7,stroke:#4a148c,stroke-width:2px
    style Detail fill:#bbdefb,stroke:#0d47a1,stroke-width:2px
    style Hidden fill:#c8e6c9,stroke:#2e7d32,stroke-dasharray: 5 5
```

The basic idea is to have two controllers:

1. **The Master (Ordenes):** Controls general information (date, customer, total).
2. **The Detail (Ordenesdetalles):** Controls the list of products within that order.

The demo store controllers keep their original Spanish names in the code (`Tienda\Ordenes`, `Tienda\Ordenesdetalles`), so this guide uses those exact names.

---

## 1. Configuring the Master (`Ordenes` Controller)

This is the "parent" of the relationship. Here we define the invoice header.

- **Basic Configuration:** We tell Ragnos to use the `orders` table and that the primary key is `orderNumber`.
- **Fields:** We define normal fields like date (`orderDate`), status (`status`), and customer (`customerNumber`).
- **Total Field (Calculated):** To show the order total without saving it manually, we use a small SQL query within the field configuration. This query sums `quantity * price` from the details table.
- **Activate Detail Mode:**
  There is a key line you must add to your master controller to warn that it will have "children". You can pass a single controller or an array of multiple controllers to automatically create tabs:

  **For a single detail:**

  ```php
  $this->setDetailsController('Tienda\Ordenesdetalles');
  ```

  This tells Ragnos that the `Ordenesdetalles` controller will handle the details related to each order.

  **For multiple details (Tabs):**
  If your main record has multiple types of details (e.g., products and a history log), you can pass an array of controllers. Ragnos will automatically generate a tabbed interface (nav-tabs) to display each detail separately:

  ```php
  $this->setDetailsController([
      'Tienda\Ordenesdetalles',
      'Tienda\Ordeneshistorial'
  ]);
  ```

  The relationship is based on the `orderNumber` field in these controllers being the same, and this is the primary key in the master.

## 2. Configuring the Detail (`Ordenesdetalles` Controller)

This is the "child". It controls each product line.

- **The Hidden Field Trick:**
  We need each product to know which order it belongs to. For that, in the `orderNumber` field of the detail, we do two things:
  1. We set it as `hidden` so the user doesn't touch it.
  2. We assign the default value `$this->master`.
     _What does this do?_ When you create a detail from order #100, Ragnos automatically fills this field with the number 100.
- **Filter Data:**
  We don't want to see _all_ products from _all_ orders. In the `_filters()` method, we add a rule so that only products matching the current master ID (`$this->master`) are loaded.

  ```php
  function _filters()
  {
      $this->modelo->builder()->where('orderNumber', $this->master);
  }
  ```

  In this snippet, `$this->modelo->builder()` allows you to interact directly with the SQL query that the grid will generate. The `$this->master` variable is automatically injected by the framework when it detects that this controller is running as a "child", and contains the ID of the record being viewed in the master (in this case, the order number).

- **Update Changes:**
  We use special functions (called _hooks_) like `_afterInsert` or `_afterUpdate` to clear the cache. This ensures that if you add a product, the main order total is recalculated correctly.
  👉 **[See Hooks Guide](../avanzado/hooks.md)**

## Custom JavaScript Hooks (Optional)

The following function has been added to the custom.js file:

```javascript
// with every change in the order details table
// recalculate the order total
function _OrdenesdetallesOnChange(table) {
  const order = document.querySelector("input[name='orderNumber']").value;
  getObject("tienda/ordenes/calculatotal", { orden: order }, function (data) {
    document.querySelector('input[name="total"]').value = data.total;
  });
}
```

This function runs every time there is a change in the order details table. What it does is:

1. Gets the current order number from the hidden field.
2. Calls an endpoint (`tienda/ordenes/calculatotal`) to recalculate the order total.
3. Updates the `total` field on screen with the new value.

This way, the total is always up to date every time a product is added, modified, or deleted in the order.

This function is linked automatically thanks to the naming convention: `_ControllerNameOnChange`, where the controller name is the short name of the PHP class, respecting upper and lower case.

## Workflow Summary

1. **You open an Order:** You see the general data (Master).
2. **The system checks:** Does this order already exist?
   - **Yes:** It automatically loads the table of related products at the bottom of the screen.
   - **No:** It hides the products section until you save the order for the first time.
3. **You add a product:** When creating a line in the details table, the system invisibly attaches the parent order number.
4. **You save:** When the detail is saved, the system updates the totals and everything stays in sync.

And that's it! With these steps, two tables work as a single integrated screen.
