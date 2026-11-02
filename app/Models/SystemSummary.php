<?php

declare(strict_types=1);

namespace App\Models;

use Throwable;

/**
 * Indicadores del sistema para el panel de inicio genérico, el que se muestra
 * cuando la demo Classicmodels no está instalada. Solo usa las tablas de
 * sampledatabase/ragnos_base.sql.
 */
class SystemSummary
{
    /**
     * @return array{usuarios:int, activos:int, grupos:int, cambios7dias:int}
     */
    public function counters(): array
    {
        $db = db_connect();

        return [
            'usuarios'     => $this->safeCount(static fn () => $db->table('gen_usuarios')->countAllResults()),
            'activos'      => $this->safeCount(static fn () => $db->table('gen_usuarios')->where('usu_activo', 'S')->countAllResults()),
            'grupos'       => $this->safeCount(static fn () => $db->table('gen_gruposdeusuarios')->countAllResults()),
            'cambios7dias' => $this->safeCount(static fn () => $db->table('gen_audit_logs')
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->countAllResults()),
        ];
    }

    /**
     * Últimos movimientos registrados en la bitácora de auditoría.
     *
     * @return list<array<string, mixed>>
     */
    public function recentActivity(int $limit = 8): array
    {
        try {
            return db_connect()->table('gen_audit_logs a')
                ->select('a.table_name, a.record_id, a.action, a.created_at, u.usu_nombre')
                ->join('gen_usuarios u', 'u.usu_id = a.user_id', 'left')
                ->orderBy('a.id', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (Throwable) {
            return [];
        }
    }

    private function safeCount(callable $query): int
    {
        try {
            return (int) $query();
        } catch (Throwable) {
            return 0;
        }
    }
}
