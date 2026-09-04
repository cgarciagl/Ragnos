<?php

declare(strict_types=1);

namespace App\ThirdParty\Ragnos\Auth\Drivers;

use App\ThirdParty\Ragnos\Auth\RagnosAuthInterface;
use CodeIgniter\Session\Session;

/**
 * Class NativeAuthDriver
 *
 * Driver de autenticación nativo para Ragnos Framework.
 * Encapsula el manejo clásico de sesiones PHP, las tablas gen_usuarios y
 * gen_gruposdeusuarios, y la validación de Bearer Tokens para APIs REST.
 */
class NativeAuthDriver implements RagnosAuthInterface
{
    /**
     * Instancia del gestor de sesiones de CodeIgniter.
     */
    protected Session $session;

    /**
     * Caché en memoria del registro completo del usuario autenticado.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $userRecord = null;

    /**
     * Usuario autenticado mediante token API durante el ciclo de vida de la petición.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $apiUser = null;

    /**
     * Nombre del campo identificador primario del usuario en sesión.
     */
    protected string $campoId = 'usu_id';

    public function __construct()
    {
        helper(['url', 'App\ThirdParty\Ragnos\Helpers\utiles_helper']);
        $this->session = session();
    }

    /**
     * {@inheritDoc}
     */
    public function checkLogin(): bool
    {
        return $this->getUserId() !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function isUserInGroup(string $group): bool
    {
        $currentGroup = (string) $this->getField('gru_nombre');
        if (trim($currentGroup) === '') {
            return false;
        }

        return mb_strtolower(trim($currentGroup)) === mb_strtolower(trim($group));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserId(): ?int
    {
        // 1. Identidad establecida por Token API en la petición actual
        if ($this->apiUser !== null && isset($this->apiUser['usu_id'])) {
            return (int) $this->apiUser['usu_id'];
        }

        // 2. Identidad establecida en la Sesión Web de PHP
        $sessionId = $this->session->get($this->campoId);
        if ($sessionId !== null && $sessionId !== '') {
            return (int) $sessionId;
        }

        // 3. Fallback: Si es llamada API y aún no se ha validado el token explícitamente
        if (function_exists('isApiCall') && isApiCall()) {
            $token = $this->extractBearerToken();
            if ($token !== '' && $this->checkApiToken($token)) {
                return (int) $this->apiUser['usu_id'];
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserName(): ?string
    {
        if ($this->apiUser !== null && !empty($this->apiUser['usu_nombre'])) {
            return (string) $this->apiUser['usu_nombre'];
        }

        $sessionName = $this->session->get('usu_nombre');
        if ($sessionName !== null && $sessionName !== '') {
            return (string) $sessionName;
        }

        $fieldName = (string) $this->getField('usu_nombre');
        return $fieldName !== '' ? $fieldName : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserEmail(): ?string
    {
        $email = (string) $this->getField('usu_email');
        if ($email !== '') {
            return $email;
        }

        $login = (string) $this->getField('usu_login');
        if ($login !== '' && filter_var($login, FILTER_VALIDATE_EMAIL) !== false) {
            return $login;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function logout(): void
    {
        $this->session->remove([$this->campoId, 'usu_nombre', 'gru_nombre', 'usu_token']);
        $this->session->destroy();
        $this->userRecord = null;
        $this->apiUser    = null;
    }

    /**
     * {@inheritDoc}
     */
    public function checkApiToken(string $token): bool
    {
        if (trim($token) === '') {
            return false;
        }

        try {
            $db   = \Config\Database::connect();
            $user = $db->table('gen_usuarios u')
                ->select('u.*, g.gru_nombre')
                ->join('gen_gruposdeusuarios g', 'g.gru_id = u.usu_grupo', 'inner')
                ->where('u.usu_token', $token)
                ->where('u.usu_activo', 'S')
                ->get()
                ->getRowArray();

            if ($user !== null) {
                $this->apiUser    = $user;
                $this->userRecord = $user;
                return true;
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
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
        $id = $this->getUserId();
        if ($id === null) {
            return '';
        }

        if ($this->userRecord === null) {
            try {
                $db               = \Config\Database::connect();
                $this->userRecord = $db->table('gen_usuarios u')
                    ->select('u.*, g.gru_nombre')
                    ->join('gen_gruposdeusuarios g', 'g.gru_id = u.usu_grupo', 'left')
                    ->where('u.usu_id', $id)
                    ->get()
                    ->getRowArray() ?: [];
            } catch (\Throwable) {
                $this->userRecord = [];
            }
        }

        return $this->userRecord[$field] ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function checkUserInGroup(string|array $grupos): void
    {
        if (!$this->checkLogin()) {
            $this->session->set('bef_uri', current_url());
            redirectAndDie('admin/login', 401);
        }

        $groupName    = mb_strtolower(trim((string) $this->getField('gru_nombre')));
        $targetGroups = array_map(
            static fn($g) => mb_strtolower(trim((string) $g)),
            (array) $grupos
        );

        if (!in_array($groupName, $targetGroups, true)) {
            redirectAndDie('admin/index', 403);
        }
    }

    /**
     * Extrae el token Bearer del header Authorization del request entrante.
     */
    protected function extractBearerToken(): string
    {
        $header = trim(request()->getHeaderLine('Authorization'));
        if (preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            return $matches[1];
        }

        return $header;
    }
}
