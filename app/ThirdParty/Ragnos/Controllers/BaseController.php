<?php

declare(strict_types=1);

namespace App\ThirdParty\Ragnos\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\ThirdParty\Ragnos\OpenApi\OpenApiDiscovery;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    protected $session;

    /** @var object|null Database connection assigned during controller initialization. */
    protected $db;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        helper('App\ThirdParty\Ragnos\Helpers\utiles_helper');

        $this->session = \Config\Services::session();
        $this->db      = db_connect();
    }

    /**
     * Verifica la autenticación del usuario. Si no está autenticado,
     * deniega el acceso con 401 (API) o redirige al login (Web).
     */
    public function checkLogin(): void
    {
        if (OpenApiDiscovery::isActive()) {
            return;
        }

        $auth = service('Admin_aut');

        if (isApiCall()) {
            $token = $this->getAuthorizationToken();
            if (!$auth->checkApiToken($token)) {
                $this->denyApiAccess(401, 'Unauthorized');
            }
            return;
        }

        if (!$auth->checkLogin()) {
            $this->session->set('bef_uri', current_url());
            redirectAndDie('admin/login', 401);
        }
    }

    /**
     * Valida un token API utilizando el servicio de autenticación desacoplado.
     */
    public function validarToken(string $token): bool
    {
        return service('Admin_aut')->checkApiToken($token);
    }

    /**
     * Valida si un token API pertenece a un usuario dentro de los grupos especificados.
     */
    public function validarTokenEnGrupos(string $token, string|array $grupos): bool
    {
        $auth = service('Admin_aut');
        if (!$auth->checkApiToken($token)) {
            return false;
        }

        foreach ((array) $grupos as $group) {
            if ($auth->isUserInGroup((string) $group)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica que el usuario pertenezca a uno de los grupos especificados.
     * Si no cumple la condición, deniega acceso con 403 (o 401 si no está autenticado).
     */
    public function checkUserInGroup(string|array $grupos): void
    {
        if (OpenApiDiscovery::isActive()) {
            return;
        }

        $auth = service('Admin_aut');

        if (isApiCall()) {
            $token = $this->getAuthorizationToken();
            if (!$auth->checkApiToken($token)) {
                $this->denyApiAccess(401, 'Unauthorized');
            }

            $allowed = false;
            foreach ((array) $grupos as $group) {
                if ($auth->isUserInGroup((string) $group)) {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                $this->denyApiAccess(403, 'Forbidden');
            }

            return;
        }

        if (!$auth->checkLogin()) {
            $this->session->set('bef_uri', current_url());
            redirectAndDie('admin/login', 401);
        }

        $allowed = false;
        foreach ((array) $grupos as $group) {
            if ($auth->isUserInGroup((string) $group)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            redirectAndDie('admin/index', 403);
        }
    }

    private function getAuthorizationToken(): string
    {
        $authorization = trim(request()->getHeaderLine('Authorization'));
        if (preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches)) {
            return $matches[1];
        }

        return $authorization;
    }

    private function normalizeGroups(string|array $groups): array
    {
        return array_values(array_unique(array_map([$this, 'normalizeGroup'], (array) $groups)));
    }

    private function normalizeGroup(string $group): string
    {
        return mb_strtolower(trim($group));
    }

    private function denyApiAccess(int $statusCode, string $message): never
    {
        service('response')
            ->setStatusCode($statusCode)
            ->setJSON(['error' => $message])
            ->send();

        exit;
    }
}
