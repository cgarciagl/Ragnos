<?php

namespace App\ThirdParty\Ragnos\Controllers;

class Ragnos
{

    public static $CI = NULL;

    function __construct()
    {
        Ragnos::get_CI();
    }

    static public function loadDefaults()
    {
        helper(array('url', 'array'));
        helper('App\ThirdParty\Ragnos\Helpers\utiles_helper');
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
    }

    static public function config()
    {
        $config = config('RagnosConfig');
        return $config;
    }

    static public function getHeaderScript()
    {
        Ragnos::loadDefaults();
        echo view('App\ThirdParty\Ragnos\Views\ragnos/headerscript');
    }

    static public function loadScriptFiles()
    {
        Ragnos::get_CI();
        echo view('App\ThirdParty\Ragnos\Views\ragnos/scriptfiles');
    }

    static public function getHeaderAll()
    {
        Ragnos::getHeaderScript();
        Ragnos::loadScriptFiles();
    }

    static public function get_CI()
    {
        if (Ragnos::$CI == NULL) {
            Ragnos::$CI = (object) ['property' => 'Here we go'];
        }
        return Ragnos::$CI;
    }
}
