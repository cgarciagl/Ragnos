<?php

declare(strict_types=1);

namespace App\Services;

use App\ThirdParty\Ragnos\Auth\RagnosAuthInterface;
use CodeIgniter\Config\BaseService;

/**
 * Class Admin_aut
 *
 * Proxy / Adaptador de retrocompatibilidad para el servicio de autenticación de Ragnos.
 * Mantiene la compatibilidad 100% con código legado delegando todas las llamadas al
 * driver configurado en el contenedor de servicios (service('Admin_aut')).
 */
class Admin_aut extends BaseService implements RagnosAuthInterface
{
    /**
     * Retorna la instancia activa del servicio de autenticación.
     */
    public static function getInstance(): RagnosAuthInterface
    {
        return service('Admin_aut');
    }

    /**
     * Retorna el ID del usuario actual de manera estática (compatibilidad legacy).
     */
    public static function id(): ?int
    {
        return service('Admin_aut')->getUserId();
    }

    /**
     * Obtiene el driver activo desde el contenedor de servicios.
     */
    protected function getDriver(): RagnosAuthInterface
    {
        return service('Admin_aut');
    }

    /**
     * {@inheritDoc}
     */
    public function checkLogin(): bool
    {
        return $this->getDriver()->checkLogin();
    }

    /**
     * {@inheritDoc}
     */
    public function isUserInGroup(string $group): bool
    {
        return $this->getDriver()->isUserInGroup($group);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserId(): ?int
    {
        return $this->getDriver()->getUserId();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserName(): ?string
    {
        return $this->getDriver()->getUserName();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserEmail(): ?string
    {
        return $this->getDriver()->getUserEmail();
    }

    /**
     * {@inheritDoc}
     */
    public function logout(): void
    {
        $this->getDriver()->logout();
    }

    /**
     * {@inheritDoc}
     */
    public function checkApiToken(string $token): bool
    {
        return $this->getDriver()->checkApiToken($token);
    }

    // -------------------------------------------------------------------------
    // Métodos de Retrocompatibilidad (Legacy API)
    // -------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     */
    public function isLoggedIn(): bool
    {
        return $this->checkLogin();
    }

    /**
     * {@inheritDoc}
     */
    public function name(): ?string
    {
        return $this->getUserName();
    }

    /**
     * {@inheritDoc}
     */
    public function getField(string $field): mixed
    {
        return $this->getDriver()->getField($field);
    }

    /**
     * {@inheritDoc}
     */
    public function checkUserInGroup(string|array $grupos): void
    {
        $this->getDriver()->checkUserInGroup($grupos);
    }

    /**
     * Delegación dinámica para cualquier método adicional del driver activo.
     *
     * @param string $method
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->getDriver()->{$method}(...$arguments);
    }
}
