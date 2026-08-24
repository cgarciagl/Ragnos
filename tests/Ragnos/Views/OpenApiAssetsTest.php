<?php

namespace Tests\Ragnos\Views;

use App\Controllers\OpenApi;
use CodeIgniter\Test\CIUnitTestCase;

final class OpenApiAssetsTest extends CIUnitTestCase
{
    public function testSwaggerViewUsesOnlyLocalAssets(): void
    {
        $view = file_get_contents(HOMEPATH . 'app/Views/openapi/docs.php');

        $this->assertIsString($view);
        $this->assertStringContainsString("assets/js/swagger-ui-bundle.js", $view);
        $this->assertStringContainsString("assets/js/swagger-ui-standalone-preset.js", $view);
        $this->assertStringContainsString("assets/css/swagger-ui.css", $view);
        $this->assertStringNotContainsString('cdn.', strtolower($view));
        $this->assertFileExists(HOMEPATH . 'content/assets/js/swagger-ui-bundle.js');
        $this->assertFileExists(HOMEPATH . 'content/assets/js/swagger-ui-standalone-preset.js');
        $this->assertFileExists(HOMEPATH . 'content/assets/css/swagger-ui.css');
    }

    public function testOpenApiConfigurationAndRoutesAreDeclared(): void
    {
        $config = file_get_contents(HOMEPATH . 'app/Config/RagnosConfig.php');
        $routes = file_get_contents(HOMEPATH . 'app/Config/Routes.php');

        $this->assertStringContainsString('Ragnos_openapi_enabled', $config);
        $this->assertStringContainsString('Ragnos_openapi_controllers', $config);
        $this->assertStringContainsString("openapi.json", $routes);
        $this->assertStringContainsString("openapi.yaml", $routes);
        $this->assertStringContainsString("OpenApi::docs", $routes);
    }

    public function testDocumentationIsPublicInDevelopmentAndDeniedWithoutAdminInProductionMode(): void
    {
        $config = config('RagnosConfig');
        $originalPublic = $config->Ragnos_openapi_public;

        try {
            $controller = new OpenApi();
            $method = new \ReflectionMethod($controller, 'canExposeDocumentation');
            $method->setAccessible(true);

            $config->Ragnos_openapi_public = true;
            $this->assertTrue($method->invoke($controller));

            $config->Ragnos_openapi_public = false;
            $this->assertFalse($method->invoke($controller));
        } finally {
            $config->Ragnos_openapi_public = $originalPublic;
        }
    }
}
