<?php

namespace App\ThirdParty\Ragnos\Models\Fields;

use App\ThirdParty\Ragnos\Models\Fields\RField;

class RSwitchField extends RField
{
    protected mixed $onValue = '1';
    protected mixed $offValue = '0';

    public function getOnValue()
    {
        return $this->onValue;
    }

    public function setOnValue($value)
    {
        $this->onValue = $value;
    }

    public function getOffValue()
    {
        return $this->offValue;
    }

    public function setOffValue($value)
    {
        $this->offValue = $value;
    }

    public function loadFromArray(array $array): void
    {
        parent::loadFromArray($array);
        if (isset($array['onValue'])) {
            $this->setOnValue($array['onValue']);
        }
        if (isset($array['offValue'])) {
            $this->setOffValue($array['offValue']);
        }
    }

    public function loadVars(): array
    {
        $vars             = parent::loadVars();
        $vars['onValue']  = $this->getOnValue();
        $vars['offValue'] = $this->getOffValue();
        return $vars;
    }
}
