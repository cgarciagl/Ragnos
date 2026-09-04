<?php

declare(strict_types=1);

namespace App\ThirdParty\Ragnos\Auth;

/**
 * Interface RagnosAuthInterface
 *
 * Contrato de autenticación y autorización para Ragnos Framework.
 * Permite desacoplar los mecanismos de autenticación (sesiones nativas,
 * CodeIgniter Shield, JWT, etc.) manteniendo compatibilidad total con la API del framework.
 */
interface RagnosAuthInterface
{
    /**
     * Verifica si existe un usuario autenticado actualmente.
     *
     * @return bool True si el usuario tiene sesión/identidad activa, false en caso contrario.
     */
    public function checkLogin(): bool;

    /**
     * Evalúa si el usuario autenticado pertenece al grupo/rol especificado.
     *
     * @param string $group Nombre o identificador del grupo.
     * @return bool True si pertenece al grupo, false si no pertenece o no está autenticado.
     */
    public function isUserInGroup(string $group): bool;

    /**
     * Obtiene el identificador único (ID) del usuario autenticado.
     *
     * @return int|null ID numérico del usuario o null si no hay sesión activa.
     */
    public function getUserId(): ?int;

    /**
     * Obtiene el nombre completo o de usuario del usuario autenticado.
     *
     * @return string|null Nombre del usuario o null si no hay sesión activa.
     */
    public function getUserName(): ?string;

    /**
     * Obtiene la dirección de correo electrónico del usuario autenticado.
     *
     * @return string|null Email del usuario o null si no está disponible.
     */
    public function getUserEmail(): ?string;

    /**
     * Cierra la sesión del usuario actual e invalida las credenciales activas.
     *
     * @return void
     */
    public function logout(): void;

    /**
     * Valida un token Bearer para peticiones API.
     * Si es válido, establece la identidad del usuario para el ciclo de vida de la petición.
     *
     * @param string $token Token de autenticación a verificar.
     * @return bool True si el token es válido y está activo, false en caso contrario.
     */
    public function checkApiToken(string $token): bool;

    // -------------------------------------------------------------------------
    // Métodos de Retrocompatibilidad (Legacy API)
    // -------------------------------------------------------------------------

    /**
     * Alias de retrocompatibilidad para checkLogin().
     *
     * @return bool
     */
    public function isLoggedIn(): bool;

    /**
     * Alias de retrocompatibilidad para getUserName().
     *
     * @return string|null
     */
    public function name(): ?string;

    /**
     * Obtiene el valor de un campo específico del registro del usuario autenticado.
     *
     * @param string $field Nombre de la columna o propiedad (ej. 'gru_nombre', 'usu_login').
     * @return mixed Valor del campo o cadena vacía si no existe.
     */
    public function getField(string $field): mixed;

    /**
     * Verifica que el usuario pertenezca a uno de los grupos permitidos.
     * Si no cumple la condición, interrumpe la ejecución redirigiendo con código HTTP 401 o 403.
     *
     * @param string|array<string> $grupos Nombre de grupo o arreglo de grupos permitidos.
     * @return void
     */
    public function checkUserInGroup(string|array $grupos): void;
}

