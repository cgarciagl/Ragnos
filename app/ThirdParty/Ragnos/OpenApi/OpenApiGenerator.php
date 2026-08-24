<?php

namespace App\ThirdParty\Ragnos\OpenApi;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;
use InvalidArgumentException;

final class OpenApiGenerator
{
    public function __construct(private OpenApiRegistry $registry)
    {
    }

    public function generate(string $serverUrl): array
    {
        $config = config('RagnosConfig');
        $spec = $this->baseDocument($config->Ragnos_openapi_title, $config->Ragnos_openapi_version, $serverUrl);

        foreach ($this->registry->all() as $entry) {
            $controller = $this->discoverController($entry['class']);
            $spec['tags'][] = ['name' => $entry['tag'] ?? $controller->getTitle() ?: $controller->getClassName()];
            $this->appendDatasetPaths($spec, $entry, $controller);
        }

        return $spec;
    }

    private function baseDocument(string $title, string $version, string $serverUrl): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => $title,
                'version' => $version,
                'description' => 'API REST híbrida generada por Ragnos.',
            ],
            'servers' => [['url' => rtrim($serverUrl, '/')]],
            'security' => [['BearerAuth' => []]],
            'tags' => [],
            'paths' => [
                '/admin/login' => $this->loginPath(),
            ],
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Ragnos token',
                    ],
                ],
                'schemas' => $this->commonSchemas(),
            ],
        ];
    }

    private function discoverController(string $class): RDatasetController
    {
        if (!is_subclass_of($class, RDatasetController::class)) {
            throw new InvalidArgumentException("{$class} no es un RDatasetController.");
        }

        OpenApiDiscovery::begin();
        try {
            $controller = new $class();
        } finally {
            OpenApiDiscovery::end();
        }

        return $controller;
    }

    private function appendDatasetPaths(array &$spec, array $entry, RDatasetController $controller): void
    {
        $model = $controller->getModel();
        $schemaName = $this->schemaName($entry, $controller);
        $tag = $entry['tag'] ?? $controller->getTitle() ?: $controller->getClassName();
        $spec['components']['schemas'][$schemaName] = $this->recordSchema($model);

        $path = '/' . ltrim($entry['path'], '/');
        $spec['paths'][$path] = $this->collectionOperations($controller, $schemaName, $tag);
        $saveOperation = $this->saveOperation($controller, $schemaName, $tag);
        if ($saveOperation !== []) {
            $spec['paths']["{$path}/save"] = $saveOperation;
        }
        $spec['paths']["{$path}/delete/{id}"] = $this->deleteOperation($model->primaryKey, $tag, $controller);
        $spec['paths']["{$path}/history/{id}"] = $this->historyOperation($tag, $controller);
        $spec['paths']["{$path}/getFieldsConfig"] = $this->fieldsConfigOperation($tag, $controller);

        $spec = $this->merge($spec, $controller->getOpenApiDefinition());
    }

    private function collectionOperations(RDatasetController $controller, string $schemaName, string $tag): array
    {
        $operations = [
            'get' => [
                'tags' => [$tag],
                'summary' => 'Lista registros con búsqueda, orden y paginación',
                'operationId' => $this->operationPrefix($controller) . 'List',
                'parameters' => $this->listParameters(),
                'responses' => $this->responses(['200' => $this->jsonResponse('Listado de registros', $this->listSchema($schemaName))]),
                'security' => [['BearerAuth' => []]],
            ],
        ];

        return $operations;
    }

    private function saveOperation(RDatasetController $controller, string $schemaName, string $tag): array
    {
        if (!$controller->canInsert() && !$controller->canUpdate()) {
            return [];
        }

        return [
            'post' => [
                'tags' => [$tag],
                'summary' => 'Crea o actualiza un registro',
                'operationId' => $this->operationPrefix($controller) . 'Save',
                'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['$ref' => "#/components/schemas/{$schemaName}"]]]],
                'responses' => $this->responses([
                    '200' => $this->jsonResponse('Registro actualizado', ['$ref' => '#/components/schemas/MutationResponse']),
                    '201' => $this->jsonResponse('Registro creado', ['$ref' => '#/components/schemas/MutationResponse']),
                ]),
                'security' => [['BearerAuth' => []]],
            ],
        ];
    }

    private function deleteOperation(string $primaryKey, string $tag, RDatasetController $controller): array
    {
        return ['delete' => [
            'tags' => [$tag],
            'summary' => 'Elimina un registro',
            'operationId' => $this->operationPrefix($controller) . 'Delete',
            'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'], 'description' => "Valor de {$primaryKey}"],],
            'responses' => $this->responses(['200' => $this->jsonResponse('Registro eliminado', ['$ref' => '#/components/schemas/MutationResponse'])]),
            'security' => [['BearerAuth' => []]],
        ]];
    }

    private function historyOperation(string $tag, RDatasetController $controller): array
    {
        return ['get' => [
            'tags' => [$tag],
            'summary' => 'Consulta el historial de auditoría',
            'operationId' => $this->operationPrefix($controller) . 'History',
            'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']]],
            'responses' => $this->responses(['200' => $this->jsonResponse('Historial', ['type' => 'object', 'properties' => ['data' => ['type' => 'array', 'items' => ['type' => 'object']]]])]),
            'security' => [['BearerAuth' => []]],
        ]];
    }

    private function fieldsConfigOperation(string $tag, RDatasetController $controller): array
    {
        return ['get' => [
            'tags' => [$tag],
            'summary' => 'Obtiene la configuración de campos',
            'operationId' => $this->operationPrefix($controller) . 'FieldsConfig',
            'responses' => $this->responses(['200' => $this->jsonResponse('Configuración de campos', ['type' => 'object'])]),
            'security' => [['BearerAuth' => []]],
        ]];
    }

    private function loginPath(): array
    {
        return ['post' => [
            'tags' => ['Authentication'],
            'summary' => 'Inicia una sesión API',
            'operationId' => 'login',
            'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['usuario', 'pword'], 'properties' => ['usuario' => ['type' => 'string'], 'pword' => ['type' => 'string', 'format' => 'password']]]]]],
            'responses' => $this->responses(['200' => $this->jsonResponse('Sesión iniciada', ['$ref' => '#/components/schemas/LoginResponse'])]),
            'security' => [],
        ]];
    }

    private function listParameters(): array
    {
        return [
            ['name' => 'start', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 0, 'default' => 0]],
            ['name' => 'length', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 10]],
            ['name' => 'search[value]', 'in' => 'query', 'schema' => ['type' => 'string']],
            ['name' => 'sOnlyField', 'in' => 'query', 'schema' => ['type' => 'string']],
            ['name' => 'sFilter', 'in' => 'query', 'schema' => ['type' => 'string', 'description' => 'Filtros estructurados codificados en Base64.']],
            ['name' => 'order[0][name]', 'in' => 'query', 'schema' => ['type' => 'string']],
            ['name' => 'order[0][dir]', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['asc', 'desc'], 'default' => 'asc']],
        ];
    }

    private function recordSchema(object $model): array
    {
        $properties = [];
        $required = [];
        foreach ($model->ofieldlist as $field) {
            $properties[$field->getFieldName()] = $this->fieldSchema($field);
            if ($field->isRequired()) {
                $required[] = $field->getFieldName();
            }
        }

        if (!isset($properties[$model->primaryKey])) {
            $properties[$model->primaryKey] = ['type' => 'string', 'readOnly' => true];
        }

        $schema = ['type' => 'object', 'properties' => $properties, 'additionalProperties' => false];
        if ($required !== []) {
            $schema['required'] = $required;
        }
        return $schema;
    }

    private function fieldSchema(object $field): array
    {
        $type = strtolower($field->getType());
        $schema = match ($type) {
            'number', 'money' => ['type' => 'number'],
            'checkbox' => ['type' => 'boolean'],
            'date' => ['type' => 'string', 'format' => 'date'],
            'datetime' => ['type' => 'string', 'format' => 'date-time'],
            default => ['type' => 'string'],
        };
        $schema['title'] = $field->getLabel();
        return $schema;
    }

    private function schemaName(array $entry, RDatasetController $controller): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '', ($entry['id'] ?? $controller->getClassName())) . 'Record';
    }

    private function operationPrefix(RDatasetController $controller): string
    {
        return strtolower((new \ReflectionClass($controller))->getShortName());
    }

    private function listSchema(string $schemaName): array
    {
        return ['type' => 'object', 'properties' => ['status' => ['type' => 'integer'], 'data' => ['type' => 'array', 'items' => ['$ref' => "#/components/schemas/{$schemaName}"]], 'count' => ['type' => 'integer'], 'total' => ['type' => 'integer']]];
    }

    private function commonSchemas(): array
    {
        return [
            'LoginResponse' => ['type' => 'object', 'properties' => ['status' => ['type' => 'string'], 'token' => ['type' => 'string'], 'user_id' => ['type' => 'string'], 'user_name' => ['type' => 'string'], 'user_group' => ['type' => 'string']]],
            'MutationResponse' => ['type' => 'object', 'properties' => ['status' => ['type' => 'integer'], 'message' => ['type' => 'string'], 'data' => ['type' => 'object']]],
            'ErrorResponse' => ['type' => 'object', 'properties' => ['error' => ['type' => 'string'], 'message' => ['type' => 'string'], 'errors' => ['type' => 'object']]],
        ];
    }

    private function jsonResponse(string $description, array $schema): array
    {
        return ['description' => $description, 'content' => ['application/json' => ['schema' => $schema]]];
    }

    private function errorResponses(): array
    {
        return [
            '400' => $this->jsonResponse('Solicitud inválida', ['$ref' => '#/components/schemas/ErrorResponse']),
            '401' => $this->jsonResponse('No autenticado', ['$ref' => '#/components/schemas/ErrorResponse']),
            '403' => $this->jsonResponse('Sin permisos', ['$ref' => '#/components/schemas/ErrorResponse']),
            '422' => $this->jsonResponse('Error de validación', ['$ref' => '#/components/schemas/ErrorResponse']),
            '500' => $this->jsonResponse('Error interno', ['$ref' => '#/components/schemas/ErrorResponse']),
        ];
    }

    private function responses(array $successResponses): array
    {
        return array_replace($successResponses, $this->errorResponses());
    }

    private function merge(array $base, array $custom): array
    {
        foreach ($custom as $key => $value) {
            if (isset($base[$key], $value) && is_array($base[$key]) && is_array($value)) {
                $base[$key] = $this->merge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }
        return $base;
    }
}
