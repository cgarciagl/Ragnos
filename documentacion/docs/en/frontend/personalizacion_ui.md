# UI Customization

Ragnos uses **AdminLTE 3** as base for its interface, integrated with CodeIgniter 4 view system. Here we explain how to customize common elements like sidebar, logo and topbar.

## Sidebar

Main application menu resides in a static view. To add, remove or reorganize links, you must edit file:

📂 `app/Views/template/sidebar.php`

### Menu Structure

Menu uses standard HTML list with Bootstrap/AdminLTE classes.

!!! info "Available Icons"

    Ragnos includes **Bootstrap Icons** library. Browse [official icon gallery](https://icons.getbootstrap.com/) to find class codes (e.g. `bi-shop`, `bi-gear`).

**Example adding simple link:**

```php
<li class="nav-item">
    <a href="<?= site_url('process/tasks') ?>" class="nav-link">
        <i class="bi bi-check-square nav-icon"></i>
        <p>My Tasks</p>
    </a>
</li>
```

**Example dropdown submenu:**

```php
<li class="nav-item">
    <a class="nav-link">
        <i class="bi bi-shop"></i>
        <p>
            Store
            <i class="nav-arrow bi bi-chevron-right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="<?= site_url('store/products') ?>" class="nav-link">
                <i class="bi bi-box nav-icon"></i>
                <p>Products</p>
            </a>
        </li>
    </ul>
</li>
```

### Menu Access Control

You can show or hide menu elements based on logged-in user role using `Admin_aut` service.

```php
<?php
// Get auth service at start of file
$auth = service('Admin_aut');
?>

<!-- ... in menu ... -->

<?php if ($auth->isUserInGroup('administrator')): ?>
    <li class="nav-item">
        <a href="<?= site_url('users') ?>" class="nav-link"> ... </a>
    </li>
<?php endif; ?>
```

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

Here you can modify right-side links (profile, notifications) or add global search.
