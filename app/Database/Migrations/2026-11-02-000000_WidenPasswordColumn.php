<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Amplía gen_usuarios.usu_pword a VARCHAR(255) para guardar hashes de
 * password_hash(). Las contraseñas md5 existentes siguen funcionando y se
 * actualizan solas en el siguiente inicio de sesión de cada usuario.
 */
class WidenPasswordColumn extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('gen_usuarios', [
            'usu_pword' => ['name' => 'usu_pword', 'type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
        ]);
    }

    public function down(): void
    {
        // No se reduce la columna: truncaría los hashes modernos.
    }
}
