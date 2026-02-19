<?php

namespace App\Libraries;

class MenuBuilder
{
    public function getTopMenu(): array
    {
        return [
            [
                'title' => 'Inicio',
                'url'   => '#',
                'icon'  => 'bi-house-door',
            ],
            [
                'title' => 'Mi perfil',
                'url'   => site_url('admin/perfil'),
                'icon'  => 'bi-person-bounding-box',
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
                    [
                        'title' => 'Empleados',
                        'url'   => site_url('tienda/empleados'),
                        'icon'  => 'bi-person-badge',
                    ],
                    [
                        'title' => 'Lineas',
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
            ],
            [
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
            ],
        ];
    }

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
                    [
                        'title' => 'Grupos de usuarios',
                        'url'   => site_url('gruposdeusuarios'),
                        'icon'  => 'bi-people',
                    ],
                ],
            ];
        }

        if ($auth->isLoggedIn()) {
            $menu[] = ['divider' => true];

            $menu[] = [
                'title' => 'Ajuste de precios',
                'url'   => site_url('proceso/showprogress'),
                'icon'  => 'bi-gear',
            ];

            $menu[] = ['divider' => true];

            $menu[] = [
                'title' => 'Cerrar sesión',
                'url'   => site_url('admin/logout'),
                'icon'  => 'bi-door-closed',
            ];
        }

        return $menu;
    }
}
