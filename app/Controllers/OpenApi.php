<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\BaseController;
use App\ThirdParty\Ragnos\OpenApi\OpenApiGenerator;
use App\ThirdParty\Ragnos\OpenApi\OpenApiRegistry;
use Symfony\Component\Yaml\Yaml;

final class OpenApi extends BaseController
{
    public function json()
    {
        if (!$this->canExposeDocumentation()) {
            return $this->documentationDenied();
        }

        return $this->response
            ->setContentType('application/vnd.oai.openapi+json;version=3.1')
            ->setJSON($this->document());
    }

    public function yaml()
    {
        if (!$this->canExposeDocumentation()) {
            return $this->documentationDenied();
        }

        return $this->response
            ->setContentType('application/yaml')
            ->setBody(Yaml::dump($this->document(), 12, 2, Yaml::DUMP_OBJECT_AS_MAP));
    }

    public function docs()
    {
        if (!$this->canExposeDocumentation()) {
            return $this->documentationDenied();
        }

        return view('openapi/docs', [
            'specUrl' => site_url('api/openapi.json'),
        ]);
    }

    private function document(): array
    {
        return (new OpenApiGenerator(OpenApiRegistry::fromConfig()))->generate(base_url());
    }

    private function canExposeDocumentation(): bool
    {
        $config = config('RagnosConfig');
        if (!$config->Ragnos_openapi_enabled) {
            return false;
        }

        if ($config->Ragnos_openapi_public) {
            return true;
        }

        $auth = service('Admin_aut');
        return $auth->isLoggedIn() && $auth->isUserInGroup('administrador');
    }

    private function documentationDenied()
    {
        return $this->response->setStatusCode(404)->setJSON([
            'error' => 'OpenAPI documentation is not available.',
        ]);
    }
}
