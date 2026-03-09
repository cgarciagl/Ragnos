<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\RController;

class RProcessController extends RController
{
    public $requireConfirmation = false;
    public $confirmationMessage = null;

    public function __construct()
    {
        helper(['App\ThirdParty\Ragnos\Helpers\process_helper']);
    }

    public function start()
    {

    }

    public function showProgress()
    {
        $nombre = get_class($this);
        $nombre = str_replace('App\Controllers\\', '', $nombre);
        $nombre = strtolower($nombre);
        $url    = $nombre . '/start';

        $confirmMsg = $this->confirmationMessage ?? lang('Ragnos.Ragnos_confirm_process_msg');

        return view('App\ThirdParty\Ragnos\Views\process\progress', [
            'url'                 => $url,
            'requireConfirmation' => $this->requireConfirmation,
            'confirmationMessage' => $confirmMsg
        ]);
    }
}