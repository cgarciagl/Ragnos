<?php

namespace App\Libraries;

class MenuBuilder
{
    private ?object $authorization;

    public function __construct(?object $authorization = null)
    {
        $this->authorization = $authorization;
    }

    /**
     * Construye la lista de elementos de navegación principal para la barra superior (Topbar).
     * Incluye catálogos, reportes, procesos y opciones de administración según permisos.
     */
    public function getTopMenu(): array
    {
        $auth = $this->authorization ?? service('Admin_aut');
        $menu = [];

        // 1. Inicio / Dashboard
        $menu[] = [
            'title' => 'Inicio',
            'url'   => site_url('admin'),
            'icon'  => 'bi-house-door',
        ];

        // 2. Catálogos
        $menu[] = [
            'title'    => 'Catálogos',
            'icon'     => 'bi-file-spreadsheet-fill',
            'children' => [
                [
                    'title' => 'Oficinas',
                    'url'   => site_url('tienda/oficinas'),
                    'icon'  => 'bi-building',
                ],
                [
                    'title' => 'Empleados',
                    'url'   => site_url('tienda/empleados'),
                    'icon'  => 'bi-person-badge',
                ],
                [
                    'title' => 'Líneas',
                    'url'   => site_url('tienda/lineas'),
                    'icon'  => 'bi-tags',
                ],
                [
                    'title' => 'Productos',
                    'url'   => site_url('tienda/productos'),
                    'icon'  => 'bi-car-front',
                ],
                [
                    'title' => 'Clientes',
                    'url'   => site_url('tienda/clientes'),
                    'icon'  => 'bi-person',
                ],
                ['divider' => true],
                [
                    'title' => 'Pagos',
                    'url'   => site_url('tienda/pagos'),
                    'icon'  => 'bi-cash',
                ],
                [
                    'title' => 'Órdenes',
                    'url'   => site_url('tienda/ordenes'),
                    'icon'  => 'bi-send',
                ],
            ],
        ];

        // 3. Reportes
        $menu[] = [
            'title'    => 'Reportes',
            'icon'     => 'bi-graph-up',
            'children' => [
                [
                    'title' => 'Ventas por Mes',
                    'url'   => site_url('tienda/reportes/ventaspormes'),
                    'icon'  => 'bi-calendar2-week',
                ],
                [
                    'title' => 'Ventas por País',
                    'url'   => site_url('tienda/reportes/ventasporpais'),
                    'icon'  => 'bi-globe-americas',
                ],
                [
                    'title' => 'Ventas por Línea',
                    'url'   => site_url('tienda/reportes/ventasporlinea'),
                    'icon'  => 'bi-box-seam',
                ],
                [
                    'title' => 'Margen de Ganancia',
                    'url'   => site_url('tienda/reportes/margenporlinea'),
                    'icon'  => 'bi-graph-up-arrow',
                ],
                [
                    'title' => 'Estados de Cuenta',
                    'url'   => site_url('tienda/reportes/estadosdecuenta'),
                    'icon'  => 'bi-cash-coin',
                ],
                [
                    'title' => 'Mejores Empleados',
                    'url'   => site_url('tienda/reportes/mejoresempleados'),
                    'icon'  => 'bi-person-check',
                ],
                [
                    'title' => 'Prod. Menor Rotación',
                    'url'   => site_url('tienda/reportes/menorrotacion'),
                    'icon'  => 'bi-hourglass-bottom',
                ],
                ['divider' => true],
                [
                    'title' => 'Reporte de Pagos Avanzado',
                    'url'   => site_url('Tienda/Reportes/reporte_avanzado'),
                    'icon'  => 'bi-sliders',
                ],
            ],
        ];

        // 4. Procesos
        $menu[] = [
            'title'    => 'Procesos',
            'icon'     => 'bi-gear-wide-connected',
            'children' => [
                [
                    'title' => 'Ajuste de Precios',
                    'url'   => site_url('proceso/showprogress'),
                    'icon'  => 'bi-arrow-repeat',
                ],
            ],
        ];

        // 5. Administración (Solo para grupo administrador)
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

    /**
     * @deprecated La navegación ahora se gestiona globalmente en la barra superior con getTopMenu().
     */
    public function getSidebarMenu(): array
    {
        return [];
    }
}
