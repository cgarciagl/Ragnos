# UI Customization

Ragnos uses **AdminLTE 4** (based on **Bootstrap 5**) with a modern **Top Navigation Layout**, integrated with CodeIgniter 4 view system. Here we explain how to customize common elements like navigation, logo, and topbar.

## Topbar and Global Navigation

The topbar is located at:
📂 `app/Views/template/topbar.php`

All system navigation is centralized in the topbar and configured through the `MenuBuilder` class to simplify maintenance and dynamic access control.

### Navigation Menu Configuration

The menu is defined in the class:
📂 `app/Libraries/MenuBuilder.php`

This class contains the `getTopMenu()` method which returns an array with the menu structure. Each item can be a simple link or a dropdown with children (`children`), with support for user group and permission validation via `Admin_aut`.

**Example structure in `MenuBuilder`:**

```php
public function getTopMenu(): array
{
    $auth = service('Admin_aut');
    $menu = [];

    // Simple link
    $menu[] = [
        'title' => 'Home',
        'url'   => site_url('admin'),
        'icon'  => 'bi-house-door',
    ];

    // Dropdown menu
    $menu[] = [
        'title'    => 'Catalogs',
        'icon'     => 'bi-file-spreadsheet-fill',
        'children' => [
            [
                'title' => 'Offices',
                'url'   => site_url('tienda/oficinas'),
                'icon'  => 'bi-building',
            ],
            ['divider' => true],
            [
                'title' => 'Payments',
                'url'   => site_url('tienda/pagos'),
                'icon'  => 'bi-cash',
            ],
        ],
    ];

    // Role-based permissions section
    if ($auth->isUserInGroup('administrador')) {
        $menu[] = [
            'title'    => 'Administration',
            'icon'     => 'bi-shield-lock',
            'children' => [
                [
                    'title' => 'Users',
                    'url'   => site_url('usuarios'),
                    'icon'  => 'bi-person-circle',
                ],
                [
                    'title' => 'User Groups',
                    'url'   => site_url('gruposdeusuarios'),
                    'icon'  => 'bi-people',
                ],
                ['divider' => true],
                [
                    'title' => 'User Profile',
                    'url'   => site_url('admin/perfil'),
                    'icon'  => 'bi-person-badge-fill',
                ],
            ],
        ];
    }

    return $menu;
}
```

### Usage in the Topbar View

To render the menu, the `topbar.php` view uses the `menu` service:

```php
<?php foreach (service('menu')->getTopMenu() as $item): ?>
    <!-- Rendering logic for links and dropdowns -->
<?php endforeach; ?>
```

This allows adding new navigation options simply by editing `MenuBuilder` without modifying the template HTML.

## Logo and Title Customization

### Application Title

The application title appearing in the topbar and page header is configured globally in `app/Config/RagnosConfig.php`:

```php
public $Ragnos_application_title = 'My Company';
```

### Brand Logo

The brand logo is located on the left side of the topbar in `topbar.php`:

```html
<a
  class="navbar-brand d-flex align-items-center gap-2"
  href="<?= site_url('admin') ?>"
>
  <img
    src="<?= base_url('img/favicon.webp') ?>"
    alt="Logo"
    class="brand-image rounded"
    style="width: 28px; height: 28px;"
  />
  <span class="brand-text fw-bold fs-5"><?= $appTitle ?></span>
</a>
```

## Dark Mode / Light Mode Theme

The base template includes a fast theme switcher (dark/light) in the topbar with persistence in `localStorage` and native Bootstrap 5 (`data-bs-theme`) support, offering a smooth visual experience with anti-flicker loading.

### Class and Color Standards for Theme Compatibility

When creating views and components in Ragnos, Bootstrap 5 semantic classes should be used instead of hardcoded colors:

| Element                    | ❌ Avoid (Hardcoded Color)    | ✅ Use (Semantic)                   | Description                                                   |
| :------------------------- | :---------------------------- | :---------------------------------- | :------------------------------------------------------------ |
| **Main text**              | `text-dark`, `text-black`     | `text-body`                         | Automatically adapts text color to active theme.              |
| **Secondary text**         | `text-muted` with fixed hex   | `text-body-secondary`, `text-muted` | Ensures readability and proper contrast.                      |
| **Card/Block Backgrounds** | `bg-white`                    | `bg-body`, `bg-body-tertiary`       | Allows cards and containers to toggle dark/light backgrounds. |
| **Secondary Backgrounds**  | `bg-light`                    | `bg-body-secondary`                 | Provides soft contrast in both themes.                        |
| **Borders**                | `border-white`, `border-dark` | `border`, `border-secondary-subtle` | Avoids rigid or invisible borders.                            |

### Contextual CSS Rules

For custom styles or external components (DataTables, ECharts, Tom Select), use the `[data-bs-theme="dark"]` selector:

```css
/* Example of contextual Dark Mode styles */
[data-bs-theme="dark"] .my-component {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}
```
