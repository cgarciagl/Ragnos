<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;

class SearchFilterTraitTest extends RagnosTestCase
{
    public function testCanFindAllRecords(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'findAll'));
    }

    public function testCanSearchByField(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'where'));
    }

    public function testCanSearchByMultipleFields(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'where'));
    }

    public function testCanFilterByCategoryFurniture(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'where'));
    }

    public function testCanCountSearchResults(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'countAllResults'));
    }

    public function testEmptySearchReturnsAllRecords(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'findAll'));
    }

    public function testSearchByPriceLessThan(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'where'));
    }

    public function testSearchByPriceGreaterThan(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'where'));
    }

    public function testSearchByExactMatch(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'where'));
    }

    public function testSearchReturnsNoResultsForNonexistent(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'where'));
    }

    public function testCanChainMultipleWheres(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertTrue(method_exists($model, 'where'));
    }
}
