<?php

namespace App\ThirdParty\Ragnos\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
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

    public function checkLogin()
    {
        if (isApiCall()) {
            $token = $this->getAuthorizationToken();
            if (!$this->validarToken($token)) {
                $this->denyApiAccess(401, 'Unauthorized');
            }
            return;
        }

        $auth = service('Admin_aut');
        $auth->checkLogin();
    }

    public function validarToken(string $token): bool
    {
        return $this->getApiUser($token) !== null;
    }

    public function validarTokenEnGrupos(string $token, string|array $grupos): bool
    {
        $user = $this->getApiUser($token);

        return $user !== null
            && in_array($this->normalizeGroup($user['gru_nombre']), $this->normalizeGroups($grupos), true);
    }

    public function checkUserInGroup(string|array $grupos): void
    {
        if (isApiCall()) {
            $token = $this->getAuthorizationToken();
            $user  = $this->getApiUser($token);

            if ($user === null) {
                $this->denyApiAccess(401, 'Unauthorized');
            }

            if (!in_array($this->normalizeGroup($user['gru_nombre']), $this->normalizeGroups($grupos), true)) {
                $this->denyApiAccess(403, 'Forbidden');
            }

            return;
        }

        $auth = service('Admin_aut');
        $auth->checkUserInGroup($grupos);
    }

    private function getApiUser(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $db = \Config\Database::connect();

        return $db->table('gen_usuarios u')
            ->select('u.usu_id, g.gru_nombre')
            ->join('gen_gruposdeusuarios g', 'g.gru_id = u.usu_grupo')
            ->where('u.usu_token', $token)
            ->where('u.usu_activo', 'S')
            ->get()
            ->getRowArray() ?: null;
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
