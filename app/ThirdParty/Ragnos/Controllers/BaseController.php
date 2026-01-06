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
 * For security be sure to declare any new methods as protected or private.
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

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        helper('App\ThirdParty\Ragnos\Helpers\utiles_helper');

        // E.g.: $this->session = \Config\Services::session();
        $this->session = \Config\Services::session();
        $this->db      = db_connect();

    }

    public function checklogin()
    {

        // Si es API, buscar Header Authorization
        if (isApiCall(request())) {
            $token = request()->getHeaderLine('Authorization');
            // Validar token (JWT o Bearer simple)
            if (!$this->validarToken($token)) {
                // Detiene la ejecución enviando JSON 401
                die(json_encode(['error' => 'Unauthorized']));
            }
            return;
        }

        $auth = service('Admin_aut');
        $auth->checklogin();
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

    public function soloparagrupo($grupos)
    {

        // Si es API, buscar Header Authorization
        if (isApiCall(request())) {
            $token = request()->getHeaderLine('Authorization');
            // Validar token (JWT o Bearer simple)
            if (!$this->validarToken($token)) {
                // Detiene la ejecución enviando JSON 401
                die(json_encode(['error' => 'Unauthorized']));
            }
            return;
        }

        $auth = service('Admin_aut');
        $auth->soloparagrupo($grupos);
    }
}
