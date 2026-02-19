# Personalización de la Interfaz (UI)

Ragnos utiliza **AdminLTE 3** como base para su interfaz, integrado con el sistema de vistas de CodeIgniter 4. Aquí te explicamos cómo personalizar los elementos comunes como el menú lateral, el logotipo y la barra superior.

## Menú Lateral (Sidebar)

El menú lateral se encuentra en:
📂 `app/Views/template/sidebar.php`

Al igual que la barra superior, el menú lateral está centralizado en la clase `MenuBuilder`.

### Configuración del Menú Lateral

El menú se define en la clase:
📂 `app/Libraries/MenuBuilder.php`

Esta clase contiene el método `getSidebarMenu()` que devuelve un array con la estructura del menú lateral, permitiendo inyectar lógica de permisos basada en el servicio `Admin_aut`.

**Ejemplo de estructura en `MenuBuilder`:**

```php
public function getSidebarMenu(): array
{
    $auth = service('Admin_aut');
    $menu = [];

    if ($auth->isUserInGroup('administrador')) {
        $menu[] = [
            'title'    => 'Usuarios',
            'icon'     => 'bi-people',
            'children' => [
                [
                    'title' => 'Usuarios',
                    'url'   => site_url('usuarios'),
                    'icon'  => 'bi-person-circle',
                ],
            ],
        ];
    }

    return $menu;
}
```

### Uso en la Vista

El archivo `sidebar.php` utiliza el servicio `menu` para iterar sobre los elementos:

```php
<?php foreach (service('menu')->getSidebarMenu() as $item): ?>
    <!-- Lógica de renderizado del sidebar -->
<?php endforeach; ?>
```

Esto centraliza toda la navegación de la aplicación en un solo lugar, facilitando el control de accesos y la organización del código.

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

A diferencia del menú lateral, el menú principal de la barra superior está centralizado en una clase para facilitar su mantenimiento y permitir una configuración más dinámica.

### Configuración del Menú de Navegación

El menú se define en la clase:
📂 `app/Libraries/MenuBuilder.php`

Esta clase contiene el método `getTopMenu()` que devuelve un array con la estructura del menú. Cada elemento puede ser un enlace simple o un desplegable con hijos (`children`).

**Ejemplo de estructura en `MenuBuilder`:**

```php
public function getTopMenu(): array
{
    return [
        [
            'title' => 'Inicio',
            'url'   => site_url(),
            'icon'  => 'bi-house-door',
        ],
        [
            'title'    => 'Catálogos',
            'icon'     => 'bi-file-spreadsheet-fill',
            'children' => [
                [
                    'title' => 'Oficinas',
                    'url'   => site_url('tienda/oficinas'),
                    'icon'  => 'bi-building',
                ],
                ['divider' => true],
                // ... más hijos
            ],
        ],
    ];
}
```

### Uso en la Vista superior

Para renderizar el menú, se utiliza el servicio `menu` inyectado en la vista:

```php
<?php foreach (service('menu')->getTopMenu() as $item): ?>
    <!-- Lógica de renderizado -->
<?php endforeach; ?>
```

Esto permite agregar nuevas opciones de menú simplemente editando la clase `MenuBuilder` sin necesidad de modificar el HTML de la barra superior.
