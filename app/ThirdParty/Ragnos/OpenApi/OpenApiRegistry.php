<?php

namespace App\ThirdParty\Ragnos\OpenApi;

use InvalidArgumentException;

final class OpenApiRegistry
{
    public function __construct(private array $entries)
    {
        $this->validateEntries();
    }

    public static function fromConfig(): self
    {
        $config = config('RagnosConfig');
        return new self($config->Ragnos_openapi_controllers ?? []);
    }

    public function all(): array
    {
        return $this->entries;
    }

    private function validateEntries(): void
    {
        foreach ($this->entries as $entry) {
            if (!is_array($entry) || empty($entry['class']) || empty($entry['path'])) {
                throw new InvalidArgumentException('Cada registro OpenAPI requiere class y path.');
            }

            if (!class_exists($entry['class'])) {
                throw new InvalidArgumentException("El controlador OpenAPI no existe: {$entry['class']}");
            }
        }
    }
}
