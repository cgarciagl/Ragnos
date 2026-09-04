<?php

declare(strict_types=1);

namespace App\ThirdParty\Ragnos\Auth\Drivers;

use App\ThirdParty\Ragnos\Auth\RagnosAuthInterface;
use RuntimeException;

/**
 * Class ShieldAuthDriver
 *
 * Adaptador de autenticación para CodeIgniter 4 Shield.
 * Integra las APIs de Shield (auth(), auth()->user(), Bearer Tokens con el autenticador
 * de tokens, y RBAC con inGroup) asegurando paridad con la API de Ragnos Framework.
 */
class ShieldAuthDriver implements RagnosAuthInterface
{
    public function __construct()
    {
        helper(['url', 'App\ThirdParty\Ragnos\Helpers\utiles_helper']);

        if (!function_exists('auth')) {
            helper('auth');
        }
    }

    /**
     * Verifica si la librería CodeIgniter Shield está disponible en el entorno.
     */
    public static function isAvailable(): bool
    {
        return class_exists(\CodeIgniter\Shield\Auth::class) || function_exists('auth');
    }

    /**
     * {@inheritDoc}
     */
    public function checkLogin(): bool
    {
        if (!function_exists('auth')) {
            return false;
        }

        try {
            return auth()->loggedIn();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isUserInGroup(string $group): bool
    {
        if (!function_exists('auth')) {
            return false;
        }

        try {
            $user = auth()->user();
            if ($user === null) {
                return false;
            }

            return $user->inGroup($group);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getUserId(): ?int
    {
        if (!function_exists('auth')) {
            return null;
        }

        try {
            $id = auth()->id();
            return $id !== null ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getUserName(): ?string
    {
        if (!function_exists('auth')) {
            return null;
        }

        try {
            $user = auth()->user();
            if ($user === null) {
                return null;
            }

            return $user->username ?? $user->name ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getUserEmail(): ?string
    {
        if (!function_exists('auth')) {
            return null;
        }

        try {
            $user = auth()->user();
            return $user?->email ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function logout(): void
    {
        if (function_exists('auth')) {
            try {
                auth()->logout();
            } catch (\Throwable) {
                session()->destroy();
            }
        } else {
            session()->destroy();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function checkApiToken(string $token): bool
    {
        if (!function_exists('auth') || trim($token) === '') {
            return false;
        }

        try {
            return auth('tokens')->attempt(['token' => $token])->isOK();
        } catch (\Throwable) {
            return false;
        }
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
    public function id(): ?int
    {
        return $this->getUserId();
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
        if (!function_exists('auth')) {
            return '';
        }

        try {
            $user = auth()->user();
            if ($user === null) {
                return '';
            }

            if ($field === 'gru_nombre') {
                if (method_exists($user, 'getGroups')) {
                    $groups = $user->getGroups();
                    return !empty($groups) ? (string) $groups[0] : '';
                }
                return '';
            }

            return $user->{$field} ?? '';
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function checkUserInGroup(string|array $grupos): void
    {
        if (!$this->checkLogin()) {
            session()->set('bef_uri', current_url());
            redirectAndDie('admin/login', 401);
        }

        $allowed = false;
        foreach ((array) $grupos as $group) {
            if ($this->isUserInGroup((string) $group)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            redirectAndDie('admin/index', 403);
        }
    }
}

