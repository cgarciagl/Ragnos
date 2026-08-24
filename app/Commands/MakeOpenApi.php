<?php

namespace App\Commands;

use App\ThirdParty\Ragnos\OpenApi\OpenApiGenerator;
use App\ThirdParty\Ragnos\OpenApi\OpenApiRegistry;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Symfony\Component\Yaml\Yaml;

final class MakeOpenApi extends BaseCommand
{
    protected $group = 'Ragnos';
    protected $name = 'ragnos:openapi';
    protected $description = 'Valida o exporta la especificación OpenAPI de Ragnos.';
    protected $usage = 'ragnos:openapi [options]';
    protected $options = [
        '--format' => 'Formato de salida: json o yaml (por defecto json).',
        '--output' => 'Archivo de salida opcional. Sin esta opción sólo valida y muestra el resultado.',
    ];

    public function run(array $params)
    {
        $format = strtolower((string) (CLI::getOption('format') ?: 'json'));
        if (!in_array($format, ['json', 'yaml'], true)) {
            CLI::error('El formato debe ser json o yaml.');
            return EXIT_ERROR;
        }

        $document = (new OpenApiGenerator(OpenApiRegistry::fromConfig()))->generate(base_url());
        $output = $format === 'yaml'
            ? Yaml::dump($document, 12, 2, Yaml::DUMP_OBJECT_AS_MAP)
            : json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        $outputPath = CLI::getOption('output');

        if ($outputPath) {
            if (file_put_contents($outputPath, $output) === false) {
                CLI::error("No se pudo escribir {$outputPath}.");
                return EXIT_ERROR;
            }
            CLI::write("OpenAPI {$format} generado en {$outputPath}", 'green');
            return EXIT_SUCCESS;
        }

        CLI::write($output);
        return EXIT_SUCCESS;
    }
}
