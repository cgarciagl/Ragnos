<?php

/**
 * Clase Base para los controladores de Ragnos
 *
 * Carga las bibliotecas necesarias estandar así como la configuración y lenguaje
 */


namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\Ragnos;
use App\ThirdParty\Ragnos\Controllers\BaseController;

use CodeIgniter\Controller;

class RController extends BaseController
{

    private $partial = 'views/default';
    private $classname = '';


    /**
     * Constructor de la clase
     */
    function __construct()
    {
        $this->classname = strtolower(get_class($this));
        $CI              = Ragnos::get_CI();
        if (!isset($CI->activeRagnosController)) {
            $CI->activeRagnosController = $this->classname;
            $CI->activeRagnosObject     = $this;
        }
        Ragnos::loadDefaults();
        $this->getPartial();
    }

    public function __get($attr)
    {
        $CI = Ragnos::get_CI();
        if (isset($this->$attr)) {
            return $this->$attr;
        } else
            if (isset($CI->$attr)) {
                return $CI->$attr;
            } else
                return NULL;
    }

    /**
     * Método que revisa si existe una vista parcial para la clase del controlador
     */
    private function getPartial()
    {
        /*  $uri = $this->classname . '/' . $this->router->method;
        if (is_file(APPPATH . 'views/' . $uri . '.php')) {
            $this->partial = $this->classname . '/' . $this->router->method;
        } */
    }

    /**
     * Devuelve el nombre de la clase del controlador
     *
     * @return string
     */
    function getClassName()
    {
        return $this->classname;
    }


    function isThisActiveController()
    {
        $CI = Ragnos::get_CI();
        if ($CI->activeRagnosController == $this->getClassName()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Helper method to return error responses with a consistent format
     * 
     * @param int $statusCode HTTP status code
     * @param string $message Error message
     * @return \CodeIgniter\HTTP\Response
     */
    private function respondWithError(int $statusCode, string $message)
    {
        return $this->response->setStatusCode($statusCode)
            ->setJSON(['success' => false, 'error' => $message]);
    }
}
