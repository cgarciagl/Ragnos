<?php

namespace Tests\Support\Controllers;

use App\ThirdParty\Ragnos\Controllers\RDataset;

/**
 * Stub concreto de RDataset para usar en tests.
 * Permite inyectar hooks personalizados sin crear clases anónimas.
 */
class TestRDataset extends RDataset
{
    /**
     * Registro de llamadas a hooks para verificación.
     */
    public array $beforeInsertCalls = [];
    public array $afterInsertCalls = [];
    public array $beforeUpdateCalls = [];
    public bool $beforeDeleteCalled = false;
    public bool $afterDeleteCalled = false;

    /**
     * Callable opcional para personalizar _beforeInsert.
     * Si no se define, usa el hook por defecto (noOp).
     *
     * @var callable|null
     */
    public $onBeforeInsert = null;

    /**
     * Callable opcional para personalizar _beforeUpdate.
     *
     * @var callable|null
     */
    public $onBeforeUpdate = null;

    /**
     * Callable opcional para personalizar _beforeDelete.
     *
     * @var callable|null
     */
    public $onBeforeDelete = null;

    public function _beforeInsert(&$dataArray): void
    {
        $this->beforeInsertCalls[] = $dataArray;
        if ($this->onBeforeInsert !== null) {
            ($this->onBeforeInsert)($dataArray);
        } else {
            // Comportamiento por defecto: pasar a mayúsculas
            $dataArray['nombre'] = strtoupper($dataArray['nombre'] ?? '');
        }
    }

    public function _afterInsert(): void
    {
        $this->afterInsertCalls[] = true;
    }

    public function _beforeUpdate(&$dataArray): void
    {
        $this->beforeUpdateCalls[] = $dataArray;
        if ($this->onBeforeUpdate !== null) {
            ($this->onBeforeUpdate)($dataArray);
        }
    }

    public function _beforeDelete(): void
    {
        $this->beforeDeleteCalled = true;
        if ($this->onBeforeDelete !== null) {
            ($this->onBeforeDelete)();
        }
    }

    public function _afterDelete(): void
    {
        $this->afterDeleteCalled = true;
    }

    /**
     * Resetea el registro de llamadas a hooks.
     */
    public function resetHookCalls(): void
    {
        $this->beforeInsertCalls  = [];
        $this->afterInsertCalls   = [];
        $this->beforeUpdateCalls  = [];
        $this->beforeDeleteCalled = false;
        $this->afterDeleteCalled  = false;
    }
}
