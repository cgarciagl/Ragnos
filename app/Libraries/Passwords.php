<?php

declare(strict_types=1);

namespace App\Libraries;

use Throwable;

/**
 * Contraseñas de gen_usuarios.
 *
 * Las contraseñas nuevas se guardan con password_hash() (bcrypt/argon según
 * PASSWORD_DEFAULT) y distinguen mayúsculas de minúsculas.
 *
 * Compatibilidad: las versiones anteriores guardaban md5(strtoupper($clave)).
 * verify() acepta ese formato y, tras un inicio de sesión correcto, el login
 * reemplaza el hash antiguo por uno moderno (ver needsRehash()). Así las
 * cuentas existentes se migran solas, sin pedir a nadie que cambie su clave.
 */
final class Passwords
{
    public static function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public static function verify(string $plain, string $stored): bool
    {
        if (self::isLegacy($stored)) {
            return hash_equals(strtolower($stored), md5(strtoupper($plain)));
        }

        return password_verify($plain, $stored);
    }

    public static function needsRehash(string $stored): bool
    {
        return self::isLegacy($stored) || password_needs_rehash($stored, PASSWORD_DEFAULT);
    }

    /** Hash md5 de 32 caracteres hexadecimales (formato anterior). */
    public static function isLegacy(string $stored): bool
    {
        return (bool) preg_match('/^[a-f0-9]{32}$/i', $stored);
    }

    /**
     * Indica si la columna usu_pword admite hashes modernos (60+ caracteres).
     * Si la base aún no se migró (VARCHAR(50)), no se reescribe el hash para
     * no truncarlo; basta con ejecutar `php spark migrate`.
     */
    public static function storageSupportsModernHashes(): bool
    {
        static $supports = null;

        if ($supports !== null) {
            return $supports;
        }

        try {
            foreach (db_connect()->getFieldData('gen_usuarios') as $field) {
                if ($field->name === 'usu_pword') {
                    return $supports = ((int) ($field->max_length ?? 0)) >= 255;
                }
            }
        } catch (Throwable) {
            // Sin acceso a los metadatos: se asume que no.
        }

        return $supports = false;
    }
}
