<?php

namespace App\ThirdParty\Ragnos\Models\Fields;

use App\ThirdParty\Ragnos\Models\Fields\rfieldDecorator;

class RIdField extends RFieldDecorator
{

    public function constructControl(): string
    {
        return view('App\ThirdParty\Ragnos\Views\rfield/idfield', $this->loadVars());
    }
}
