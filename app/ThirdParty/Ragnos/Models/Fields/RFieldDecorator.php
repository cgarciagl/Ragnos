<?php

namespace App\ThirdParty\Ragnos\Models\Fields;

use App\ThirdParty\Ragnos\Models\Fields\RField;

abstract class RFieldDecorator extends RField
{

    protected $_field;

    public function __construct(RField $Field)
    {
        parent::__construct($Field->fieldname);
        $this->_field      = $Field;
        $this->fieldname   = &$Field->fieldname;
        $this->rules       = &$Field->rules;
        $this->label       = &$Field->label;
        $this->value       = &$Field->value;
        $this->default     = &$Field->default;
        $this->placeholder = &$Field->placeholder;
        $this->tab         = &$Field->tab;
    }
}
