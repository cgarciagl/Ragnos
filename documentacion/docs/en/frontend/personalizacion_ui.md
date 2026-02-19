# UI Customization

Ragnos uses **AdminLTE 3** as base for its interface, integrated with CodeIgniter 4 view system. Here we explain how to customize common elements like sidebar, logo and topbar.

## Sidebar

Sidebar is located at:
📂 `app/Views/template/sidebar.php`

Just like the topbar, the sidebar menu is centralized in the `MenuBuilder` class.

### Sidebar Menu Configuration

The menu is defined in the class:
📂 `app/Libraries/MenuBuilder.php`

This class contains the `getSidebarMenu()` method which returns an array with the sidebar structure, allowing for permission logic based on the `Admin_aut` service.

**Example structure in `MenuBuilder`:**

```php
public function getSidebarMenu(): array
{
    $auth = service('Admin_aut');
    $menu = [];

    if ($auth->isUserInGroup('administrator')) {
        $menu[] = [
            'title'    => 'Users',
            'icon'     => 'bi-people',
            'children' => [
                [
                    'title' => 'Users',
                    'url'   => site_url('users'),
                    'icon'  => 'bi-person-circle',
                ],
            ],
        ];
    }

    return $menu;
}
```

### Usage in View

The `sidebar.php` file uses the `menu` service to iterate over items:

```php
<?php foreach (service('menu')->getSidebarMenu() as $item): ?>
    <!-- Sidebar rendering logic -->
<?php endforeach; ?>
```

This centralizes all application navigation in one place, making access control and code organization easier.

## Logo and Title Customization

### Application Title

Name appearing next to logo is configured globally in `app/Config/RagnosConfig.php`:

```php
public $Ragnos_application_title = 'My Company';
```

### Logo

Logo prints at top of `sidebar.php`:

```php
<div class="sidebar-brand">
    <a href="<?= site_url() ?>" class="brand-link">
        <!-- You can change this span for an img tag -->
        <span class="brand-text font-weight-light">
            <?= Ragnos::config()->Ragnos_application_title; ?>
        </span>
    </a>
</div>
```

To use an image:

```php
<img src="<?= base_url('content/img/my-logo.png') ?>" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
```

## Topbar

Topbar is located at:
📂 `app/Views/template/topbar.php`

Unlike the sidebar, the main menu in the topbar is centralized in a class to simplify maintenance and allow for a more dynamic configuration.

### Navigation Menu Configuration

The menu is defined in the class:
📂 `app/Libraries/MenuBuilder.php`

This class contains the `getTopMenu()` method which returns an array with the menu structure. Each element can be a simple link or a dropdown with children (`children`).

**Example structure in `MenuBuilder`:**

```php
public function getTopMenu(): array
{
    return [
        [
            'title' => 'Home',
            'url'   => site_url(),
            'icon'  => 'bi-house-door',
        ],
        [
            'title'    => 'Catalogs',
            'icon'     => 'bi-file-spreadsheet-fill',
            'children' => [
                [
                    'title' => 'Offices',
                    'url'   => site_url('store/offices'),
                    'icon'  => 'bi-building',
                ],
                ['divider' => true],
                // ... more children
            ],
        ],
    ];
}
```

### Usage in top View

To render the menu, the `menu` service is used in the view:

```php
<?php foreach (service('menu')->getTopMenu() as $item): ?>
    <!-- Rendering logic -->
<?php endforeach; ?>
```

This allows adding new menu options simply by editing the `MenuBuilder` class without needing to modify the topbar HTML.
