<?php

namespace Tests\Ragnos\OpenApi;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;
use App\ThirdParty\Ragnos\OpenApi\OpenApiGenerator;
use App\ThirdParty\Ragnos\OpenApi\OpenApiRegistry;
use CodeIgniter\Test\CIUnitTestCase;
use Symfony\Component\Yaml\Yaml;

final class OpenApiFixtureController extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();
        $this->setTitle('Fixture');
        $this->setTableName('openapi_fixture');
        $this->setIdField('id');
        $this->addField('name', ['label' => 'Name', 'rules' => 'required']);
        $this->addField('active', ['label' => 'Active', 'type' => 'checkbox', 'rules' => 'permit_empty']);
        $this->setTableFields(['name', 'active']);
    }

    public function getOpenApiDefinition(): array
    {
        return [
            'paths' => [
                '/fixture' => [
                    'get' => ['description' => 'Custom fixture description.'],
                ],
            ],
        ];
    }
}

final class OpenApiGeneratorTest extends CIUnitTestCase
{
    public function testGeneratedDocumentUsesOpenApi31AndRegisteredControllers(): void
    {
        $config = config('RagnosConfig');
        $registry = OpenApiRegistry::fromConfig();
        $this->assertNotEmpty($registry->all());
        $document = (new OpenApiGenerator(new OpenApiRegistry([
            ['id' => 'fixture', 'class' => OpenApiFixtureController::class, 'path' => '/fixture', 'tag' => 'Fixture'],
        ])))->generate('http://localhost/content');

        $this->assertSame('3.1.0', $document['openapi']);
        $this->assertSame($config->Ragnos_openapi_title, $document['info']['title']);
        $this->assertArrayHasKey('/admin/login', $document['paths']);
        $this->assertArrayHasKey('/fixture', $document['paths']);
        $this->assertArrayNotHasKey('/tienda/clientes', $document['paths']);
        $this->assertArrayHasKey('BearerAuth', $document['components']['securitySchemes']);
        $this->assertArrayHasKey('fixtureRecord', $document['components']['schemas']);
    }

    public function testDatasetPathDocumentsFilteringSortingPaginationAndCrud(): void
    {
        $document = (new OpenApiGenerator(new OpenApiRegistry([
            ['id' => 'fixture', 'class' => OpenApiFixtureController::class, 'path' => '/fixture', 'tag' => 'Fixture'],
        ])))->generate('http://localhost/content');

        $list = $document['paths']['/fixture']['get'];
        $parameterNames = array_column($list['parameters'], 'name');

        $this->assertContains('start', $parameterNames);
        $this->assertContains('length', $parameterNames);
        $this->assertContains('search[value]', $parameterNames);
        $this->assertContains('order[0][name]', $parameterNames);
        $this->assertArrayHasKey('/fixture/save', $document['paths']);
        $this->assertArrayHasKey('/fixture/delete/{id}', $document['paths']);
        $this->assertArrayHasKey('/fixture/history/{id}', $document['paths']);
        $this->assertArrayHasKey('/fixture/getFieldsConfig', $document['paths']);
        $this->assertSame(
            [200, 201, 400, 401, 403, 422, 500],
            array_keys($document['paths']['/fixture/save']['post']['responses']),
        );
        $this->assertArrayHasKey('401', $document['paths']['/fixture/delete/{id}']['delete']['responses']);
        $this->assertSame('Custom fixture description.', $list['description']);
    }

    public function testYamlOutputCanBeParsedBack(): void
    {
        $document = (new OpenApiGenerator(new OpenApiRegistry([
            ['id' => 'fixture', 'class' => OpenApiFixtureController::class, 'path' => '/fixture', 'tag' => 'Fixture'],
        ])))->generate('http://localhost/content');
        $yaml = Yaml::dump($document, 12, 2, Yaml::DUMP_OBJECT_AS_MAP);
        $decoded = Yaml::parse($yaml);

        $this->assertSame('3.1.0', $decoded['openapi']);
        $this->assertArrayHasKey('/fixture', $decoded['paths']);
        $this->assertArrayHasKey('BearerAuth', $decoded['components']['securitySchemes']);
    }
}
