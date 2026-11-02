<?php

declare(strict_types=1);

namespace Ragnos\Routing;

use CodeIgniter\Controller;
use CodeIgniter\Router\RouteCollection;
use Ragnos\Controllers\RDatasetController;
use Ragnos\Controllers\RProcessController;
use ReflectionClass;
use ReflectionMethod;

/**
 * Rutas explícitas para Ragnos.
 *
 * Ragnos funciona por defecto con el Auto Routing (Legacy) de CodeIgniter 4.
 * Esta clase permite desactivarlo ($autoRoute = false en Config\Routing) sin
 * perder ninguna pantalla: registra solo las acciones que Ragnos necesita y
 * los métodos públicos que declara cada controlador de la aplicación.
 *
 * A diferencia del Auto Routing, los métodos heredados que no son acciones
 * (setters como setSortingField(), initController(), etc.) no quedan
 * expuestos por URL.
 *
 * Uso típico en app/Config/Routes.php:
 *
 *     \Ragnos\Routing\RagnosRoutes::discover($routes);
 *
 * O, si prefieres declarar cada controlador a mano:
 *
 *     RagnosRoutes::dataset($routes, 'tienda/productos', Productos::class);
 *     RagnosRoutes::process($routes, 'proceso', Proceso::class);
 *     RagnosRoutes::controller($routes, 'admin', Admin::class);
 */
final class RagnosRoutes
{
    /**
     * Acciones de RDatasetController/RQueryController que usan las vistas,
     * el JavaScript de Ragnos y el modo API.
     */
    public const DATASET_ACTIONS = [
        'index', 'getAjaxGridData', 'getFormData', 'formProcess', 'searchByAjax',
        'search', 'tableByAjax', 'getRecordByAjax', 'ajaxdelete', 'save', 'delete',
        'history', 'getFieldsConfig', 'getTableFields', 'genericAdvancedReport',
    ];

    /** Acciones de RProcessController. */
    public const PROCESS_ACTIONS = ['showProgress', 'start'];

    /** Verbos HTTP que atienden las rutas (el modo API usa PUT/PATCH/DELETE). */
    private const VERBS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    /** Métodos públicos que nunca se exponen, aunque estén declarados. */
    private const NEVER_ROUTED = ['initController', 'remap', '_remap'];

    /**
     * Registra un controlador derivado de RDatasetController (o RQueryController).
     */
    public static function dataset(RouteCollection $routes, string $uri, string $class): void
    {
        self::controller($routes, $uri, $class, self::DATASET_ACTIONS);
    }

    /**
     * Registra un controlador derivado de RProcessController.
     */
    public static function process(RouteCollection $routes, string $uri, string $class): void
    {
        self::controller($routes, $uri, $class, self::PROCESS_ACTIONS);
    }

    /**
     * Registra las acciones indicadas más los métodos públicos que el propio
     * controlador declara (no los heredados).
     *
     * @param list<string> $actions
     */
    public static function controller(RouteCollection $routes, string $uri, string $class, array $actions = []): void
    {
        $uri     = trim($uri, '/');
        $handler = '\\' . ltrim($class, '\\');
        $methods = array_unique([...$actions, ...self::declaredPublicMethods($class)]);

        foreach ($methods as $method) {
            if ($method === 'index') {
                $routes->match(self::VERBS, $uri, $handler . '::index');
            }

            foreach (array_unique([$method, strtolower($method)]) as $segment) {
                $routes->match(self::VERBS, $uri . '/' . $segment, $handler . '::' . $method);
                $routes->match(self::VERBS, $uri . '/' . $segment . '/(:any)', $handler . '::' . $method . '/$1');
            }
        }
    }

    /**
     * Recorre una carpeta de controladores y registra cada clase según su tipo.
     *
     * La URL se forma igual que con el Auto Routing: la ruta del archivo en
     * minúsculas (Tienda/Productos.php → tienda/productos), y también con el
     * nombre de la clase (Tienda/Productos), que es como Ragnos arma sus URLs
     * internas. Los métodos se aceptan con su nombre exacto y en minúsculas.
     */
    public static function discover(
        RouteCollection $routes,
        string $namespace = 'App\\Controllers',
        ?string $directory = null,
    ): void {
        $directory ??= APPPATH . 'Controllers';
        $directory = rtrim($directory, '/\\');

        if (! is_dir($directory)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($directory) + 1, -4);
            $class    = trim($namespace, '\\') . '\\' . str_replace(['/', '\\'], '\\', $relative);

            if (! class_exists($class) || ! is_subclass_of($class, Controller::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract()) {
                continue;
            }

            // Ragnos genera sus URLs internas con el nombre de la clase
            // (Tienda/Productos/getAjaxGridData) y los menús suelen usar
            // minúsculas (tienda/productos): se registran ambas formas.
            $path = str_replace('\\', '/', $relative);

            foreach (array_unique([strtolower($path), $path]) as $uri) {
                match (true) {
                    is_subclass_of($class, RDatasetController::class) => self::dataset($routes, $uri, $class),
                    is_subclass_of($class, RProcessController::class) => self::process($routes, $uri, $class),
                    default                                          => self::controller($routes, $uri, $class),
                };
            }
        }
    }

    /**
     * Métodos públicos declarados en la propia clase (no heredados), sin los
     * que empiezan con guion bajo (hooks como _beforeInsert) ni el constructor.
     *
     * @return list<string>
     */
    private static function declaredPublicMethods(string $class): array
    {
        $methods = [];

        foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== ltrim($class, '\\')
                || $method->isStatic()
                || $method->isConstructor()
                || str_starts_with($method->name, '_')
                || in_array($method->name, self::NEVER_ROUTED, true)) {
                continue;
            }

            $methods[] = $method->name;
        }

        return $methods;
    }
}
