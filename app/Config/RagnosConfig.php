<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class RagnosConfig extends BaseConfig
{
    public $Ragnos_application_title = '🏪 Tienda';
    public $Ragnos_footer_text = '© 2024 Mi Empresa - Todos los derechos reservados.';
    public $Ragnos_theme_color = 'primary';
    public $Ragnos_all_to_uppercase = false;

    public $currency = 'USD';
    public $locale = 'es_MX';

    /**
     * Driver de autenticación activo.
     * Opciones soportadas: 'native' | 'shield'
     */
    public string $authDriver = 'native';

    public function __construct()
    {
        parent::__construct();

        $envDriver = env('ragnos.authDriver');
        if ($envDriver !== null && $envDriver !== '') {
            $this->authDriver = (string) $envDriver;
        }
    }

    public bool $Ragnos_openapi_enabled = true;
    public bool $Ragnos_openapi_public = ENVIRONMENT !== 'production';
    public string $Ragnos_openapi_title = 'Ragnos API';
    public string $Ragnos_openapi_version = '1.0.0';
    public array $Ragnos_openapi_controllers = [
        ['id' => 'clientes', 'class' => \App\Controllers\Tienda\Clientes::class, 'path' => '/tienda/clientes', 'tag' => 'Clientes'],
        ['id' => 'empleados', 'class' => \App\Controllers\Tienda\Empleados::class, 'path' => '/tienda/empleados', 'tag' => 'Empleados'],
        ['id' => 'lineas', 'class' => \App\Controllers\Tienda\Lineas::class, 'path' => '/tienda/lineas', 'tag' => 'Líneas'],
        ['id' => 'oficinas', 'class' => \App\Controllers\Tienda\Oficinas::class, 'path' => '/tienda/oficinas', 'tag' => 'Oficinas'],
        ['id' => 'ordenes', 'class' => \App\Controllers\Tienda\Ordenes::class, 'path' => '/tienda/ordenes', 'tag' => 'Órdenes'],
        ['id' => 'ordenesdetalles', 'class' => \App\Controllers\Tienda\Ordenesdetalles::class, 'path' => '/tienda/ordenesdetalles', 'tag' => 'Detalles de órdenes'],
        ['id' => 'pagos', 'class' => \App\Controllers\Tienda\Pagos::class, 'path' => '/tienda/pagos', 'tag' => 'Pagos'],
        ['id' => 'productos', 'class' => \App\Controllers\Tienda\Productos::class, 'path' => '/tienda/productos', 'tag' => 'Productos'],
        ['id' => 'usuarios', 'class' => \App\Controllers\Usuarios::class, 'path' => '/usuarios', 'tag' => 'Usuarios'],
        ['id' => 'grupos', 'class' => \App\Controllers\Gruposdeusuarios::class, 'path' => '/gruposdeusuarios', 'tag' => 'Grupos de usuarios'],
    ];
}
