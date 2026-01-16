# Personalización de la Interfaz (UI)

Ragnos utiliza **AdminLTE 3** como base para su interfaz, integrado con el sistema de vistas de CodeIgniter 4. Aquí te explicamos cómo personalizar los elementos comunes como el menú lateral, el logotipo y la barra superior.

## Menú Lateral (Sidebar)

El menú principal de la aplicación se encuentra en una vista estática. Para agregar, quitar o reorganizar enlaces, debes editar el archivo:

📂 `app/Views/template/sidebar.php`

### Estructura del Menú

El menú utiliza una lista HTML estándar con clases de Bootstrap/AdminLTE.

!!! info "Iconos Disponibles"

    Ragnos incluye la librería **Bootstrap Icons**. Puedes navegar por la [galería oficial de iconos](https://icons.getbootstrap.com/) para encontrar los códigos de clase (ej. `bi-shop`, `bi-gear`).

**Ejemplo de cómo agregar un enlace simple:**

```php
<li class="nav-item">
    <a href="<?= site_url('proceso/tareas') ?>" class="nav-link">
        <i class="bi bi-check-square nav-icon"></i>
        <p>Mis Tareas</p>
    </a>
</li>
```

**Ejemplo de un submenú desplegable:**

```php
<li class="nav-item">
    <a class="nav-link">
        <i class="bi bi-shop"></i>
        <p>
            Tienda
            <i class="nav-arrow bi bi-chevron-right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="<?= site_url('tienda/productos') ?>" class="nav-link">
                <i class="bi bi-box nav-icon"></i>
                <p>Productos</p>
            </a>
        </li>
    </ul>
</li>
```

### Control de Acceso en el Menú

Puedes mostrar u ocultar elementos del menú según el rol del usuario logueado utilizando el servicio `Admin_aut`.

```php
<?php
// Obtener el servicio de autenticación al inicio del archivo
$auth = service('Admin_aut');
?>

<!-- ... en el menú ... -->

<?php if ($auth->esdegrupo('administrador')): ?>
    <li class="nav-item">
        <a href="<?= site_url('usuarios') ?>" class="nav-link"> ... </a>
    </li>
<?php endif; ?>
```

## Personalización del Logotipo y Título

### Título de la Aplicación

El nombre que aparece junto al logo se configura globalmente en `app/Config/RagnosConfig.php`:

```php
public $Ragnos_application_title = 'Mi Empresa';
```

### Logotipo

El logo se imprime en la parte superior del archivo `sidebar.php`:

```php
<div class="sidebar-brand">
    <a href="<?= site_url() ?>" class="brand-link">
        <!-- Puedes cambiar este span por una etiqueta img -->
        <span class="brand-text font-weight-light">
            <?= Ragnos::config()->Ragnos_application_title; ?>
        </span>
    </a>
</div>
```

Para usar una imagen:

```php
<img src="<?= base_url('content/img/mi-logo.png') ?>" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
```

## Barra Superior (Topbar)

La barra superior se encuentra en:
📂 `app/Views/template/topbar.php`

Aquí puedes modificar los enlaces de la derecha (perfil, notificaciones) o agregar buscadores globales.
