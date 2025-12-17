# Plantilla oficial de nuevo Dataset

```php
<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class EjemploDataset extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        $this->checklogin();
        $this->setTitle('Ejemplo');
        $this->setTableName('example_table');
        $this->setIdField('id');
        $this->setAutoIncrement(true);

        $this->addField('name', [
            'label' => 'Nombre',
            'rules' => 'required'
        ]);

        $this->setTableFields(['name']);
    }
}
```
