<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\DemoData;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Protege los módulos de la demo (tienda/*, proceso/*).
 *
 * Si las tablas de demo_classicmodels.sql no existen, en lugar de un error de
 * base de datos el usuario regresa al inicio con un aviso, y los clientes de
 * la API reciben un 404 en JSON.
 */
class DemoDataFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (DemoData::isInstalled()) {
            return null;
        }

        if (str_contains($request->getHeaderLine('Accept'), 'application/json')) {
            return service('response')
                ->setStatusCode(404)
                ->setJSON(['error' => 'Demo data is not installed']);
        }

        return redirect()->to(site_url('admin'))
            ->with('warning', 'Este módulo pertenece a la demo de la tienda, que no está instalada.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
