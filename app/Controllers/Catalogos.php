<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\BaseController;

class Catalogos extends BaseController
{
    /**
     * Renders a catalog view for the specified controller
     *
     * @param string $controller The controller name
     * @return string The rendered view
     * @throws \RuntimeException If controller parameter is empty
     */
    private function renderCatalogoView(string $controller): string
    {
        if (empty($controller)) {
            throw new \RuntimeException('Controller name cannot be empty');
        }

        $this->checklogin();

        return view('admin/catalogo_view', [
            'controller' => $controller
        ]);
    }

}
