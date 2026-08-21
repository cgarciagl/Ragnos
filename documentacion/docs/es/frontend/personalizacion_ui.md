# Personalización de la Interfaz (UI)

Ragnos utiliza **AdminLTE 4** (basado en **Bootstrap 5**) con un esquema moderno de **Navegación Superior (_Top Navigation Layout_)**, integrado con el sistema de vistas de CodeIgniter 4. Aquí te explicamos cómo personalizar los elementos comunes como la navegación, el logotipo y la barra superior.

## Barra Superior (Topbar) y Navegación Global

La barra superior se encuentra en:
📂 `app/Views/template/topbar.php`

Toda la navegación del sistema está centralizada en la barra superior y configurada mediante la clase `MenuBuilder` para facilitar el mantenimiento y control dinámico de accesos.

### Configuración del Menú de Navegación

El menú se define en la clase:
📂 `app/Libraries/MenuBuilder.php`

Esta clase contiene el método `getTopMenu()` que devuelve un array con la estructura del menú. Cada elemento puede ser un enlace simple o un menú desplegable con hijos (`children`), con soporte para validaciones de grupos y permisos mediante `Admin_aut`.

**Ejemplo de estructura en `MenuBuilder`:**

```php
public function getTopMenu(): array
{
    $auth = service('Admin_aut');
    $menu = [];

    // Enlace simple
    $menu[] = [
        'title' => 'Inicio',
        'url'   => site_url('admin'),
        'icon'  => 'bi-house-door',
    ];

    // Menú desplegable
    $menu[] = [
        'title'    => 'Catálogos',
        'icon'     => 'bi-file-spreadsheet-fill',
        'children' => [
            [
                'title' => 'Oficinas',
                'url'   => site_url('tienda/oficinas'),
                'icon'  => 'bi-building',
            ],
            ['divider' => true],
            [
                'title' => 'Pagos',
                'url'   => site_url('tienda/pagos'),
                'icon'  => 'bi-cash',
            ],
        ],
    ];

    // Sección con control de permisos
    if ($auth->isUserInGroup('administrador')) {
        $menu[] = [
            'title'    => 'Administración',
            'icon'     => 'bi-shield-lock',
            'children' => [
                [
                    'title' => 'Usuarios',
                    'url'   => site_url('usuarios'),
                    'icon'  => 'bi-person-circle',
                ],
                [
                    'title' => 'Grupos de Usuarios',
                    'url'   => site_url('gruposdeusuarios'),
                    'icon'  => 'bi-people',
                ],
                ['divider' => true],
                [
                    'title' => 'Perfil de Usuario',
                    'url'   => site_url('admin/perfil'),
                    'icon'  => 'bi-person-badge-fill',
                ],
            ],
        ];
    }

    return $menu;
}
```

### Uso en la Vista Superior

Para renderizar el menú, la vista `topbar.php` utiliza el servicio `menu`:

```php
<?php foreach (service('menu')->getTopMenu() as $item): ?>
    <!-- Lógica de renderizado de enlaces y desplegables -->
<?php endforeach; ?>
```

Esto permite agregar nuevas opciones de navegación simplemente editando `MenuBuilder` sin necesidad de modificar el HTML de la plantilla.

## Personalización del Logotipo y Título

### Título de la Aplicación

El nombre que aparece en la barra superior y en el encabezado se configura globalmente en `app/Config/RagnosConfig.php`:

```php
public $Ragnos_application_title = 'Mi Empresa';
```

### Logotipo de Marca

El logotipo se ubica a la izquierda de la barra superior en `topbar.php`:

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

## Modo Oscuro / Modo Claro (Dark/Light Theme)

La plantilla base incorpora un selector rápido de tema claro/oscuro en la barra superior con persistencia en `localStorage` y soporte nativo de Bootstrap 5 (`data-bs-theme`), permitiendo una experiencia visual fluida sin parpadeos de carga (_anti-flicker_).

### Estándares de Clases y Colores para Compatibilidad de Tema

Al crear vistas y componentes en Ragnos, se deben utilizar las clases semánticas de Bootstrap 5 en lugar de colores fijos:

| Elemento                      | ❌ Evitar (Color Fijo)        | ✅ Usar (Semántico)                 | Descripción                                                          |
| :---------------------------- | :---------------------------- | :---------------------------------- | :------------------------------------------------------------------- |
| **Texto principal**           | `text-dark`, `text-black`     | `text-body`                         | Se adapta automáticamente al color de texto según el tema.           |
| **Texto secundario**          | `text-muted` con hex fijos    | `text-body-secondary`, `text-muted` | Mantiene legibilidad y contraste adecuado.                           |
| **Fondos de tarjeta/bloques** | `bg-white`                    | `bg-body`, `bg-body-tertiary`       | Permite que las tarjetas y contenedores alternen fondo oscuro/claro. |
| **Fondos secundarios**        | `bg-light`                    | `bg-body-secondary`                 | Proporciona contraste suave en ambos temas.                          |
| **Bordes**                    | `border-white`, `border-dark` | `border`, `border-secondary-subtle` | Evita bordes rígidos o invisibles.                                   |

### Reglas CSS Contextuales

Para estilos personalizados o componentes externos (DataTables, ECharts, Select2), se utiliza el selector `[data-bs-theme="dark"]`:

```css
/* Ejemplo de estilos contextuales para Modo Oscuro */
[data-bs-theme="dark"] .mi-componente {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}
```
