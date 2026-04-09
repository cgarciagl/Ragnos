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
            $token = request()->getHeaderLine('Authorization');
            $token = str_replace('Bearer ', '', $token);
            if (!$this->validarToken($token)) {
                die(json_encode(['error' => 'Unauthorized']));
            }
            return;
        }

        $auth = service('Admin_aut');
        $auth->checkLogin();
    }

    function validarToken($token)
    {
        $db   = \Config\Database::connect();
        $user = $db->table('gen_usuarios')
            ->where('usu_token', $token)
            ->get()
            ->getRow();
        return $user !== null;
    }

    public function checkUserInGroup($grupos)
    {
        if (isApiCall()) {
            $token = request()->getHeaderLine('Authorization');
            $token = str_replace('Bearer ', '', $token);
            if (!$this->validarToken($token)) {
                die(json_encode(['error' => 'Unauthorized']));
            }
            return;
        }

        $auth = service('Admin_aut');
        $auth->checkUserInGroup($grupos);
    }

}
