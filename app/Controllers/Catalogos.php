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

    public function usuarios()
    {
        return $this->renderCatalogoView('Usuarios');
    }

    public function gruposdeusuarios()
    {
        return $this->renderCatalogoView('Gruposdeusuarios');
    }

    public function canciones()
    {
        return $this->renderCatalogoView('Musica\Canciones');
    }

    public function artistas()
    {
        return $this->renderCatalogoView('Musica\Artistas');
    }

    public function categorias()
    {
        return $this->renderCatalogoView('Musica\Categorias');
    }

    /* Para la tienda */

    public function oficinas()
    {
        return $this->renderCatalogoView('Tienda\Oficinas');
    }

    public function empleados()
    {
        return $this->renderCatalogoView('Tienda\Empleados');
    }

    public function lineas()
    {
        return $this->renderCatalogoView('Tienda\Lineas');
    }

    public function productos()
    {
        return $this->renderCatalogoView('Tienda\Productos');
    }

    public function clientes()
    {
        return $this->renderCatalogoView('Tienda\Clientes');
    }

    public function ordenes()
    {
        return $this->renderCatalogoView('Tienda\Ordenes');
    }

    public function pagos()
    {
        return $this->renderCatalogoView('Tienda\Pagos');
    }

}
