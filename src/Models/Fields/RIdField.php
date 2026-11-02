<?php

namespace Ragnos\Models\Fields;

use Ragnos\Models\Fields\RFieldDecorator;

class RIdField extends RFieldDecorator
{

    public function constructControl(): string
    {
        return view('Ragnos\Views\rfield/idfield', $this->loadVars());
    }
}
