<?php

declare(strict_types=1);

namespace App\Libraries;

use Throwable;

/**
 * Detecta si la base de datos de demostración (Classicmodels) está instalada.
 *
 * La demo vive en sampledatabase/demo_classicmodels.sql y es opcional: una
 * instalación limpia solo necesita sampledatabase/ragnos_base.sql. El menú,
 * el panel de inicio y las rutas de la tienda consultan esta clase para no
 * mostrar módulos cuyas tablas no existen.
 */
final class DemoData
{
    /** Tablas que crea demo_classicmodels.sql. */
    public const TABLES = [
        'offices', 'employees', 'customers', 'productlines',
        'products', 'orders', 'orderdetails', 'payments',
    ];

    private static ?bool $installed = null;

    public static function isInstalled(): bool
    {
        if (self::$installed !== null) {
            return self::$installed;
        }

        try {
            $db = db_connect();

            foreach (self::TABLES as $table) {
                if (! $db->tableExists($table)) {
                    return self::$installed = false;
                }
            }

            return self::$installed = true;
        } catch (Throwable) {
            return self::$installed = false;
        }
    }

    /**
     * Quita de la lista de controladores OpenAPI los de la demo (App\Controllers\Tienda)
     * cuando sus tablas no existen, para no documentar endpoints inaccesibles.
     *
     * @param list<array<string, mixed>> $entries
     *
     * @return list<array<string, mixed>>
     */
    public static function filterOpenApiEntries(array $entries): array
    {
        if (self::isInstalled()) {
            return $entries;
        }

        return array_values(array_filter(
            $entries,
            static fn (array $entry): bool => ! str_starts_with(ltrim((string) ($entry['class'] ?? ''), '\\'), 'App\\Controllers\\Tienda\\'),
        ));
    }

    /**
     * Fuerza el resultado (pruebas) o, con null, vuelve a detectarlo.
     */
    public static function override(?bool $installed): void
    {
        self::$installed = $installed;
    }
}
