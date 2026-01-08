<?php

namespace App\ThirdParty\Ragnos\Models\Fields;

use CodeIgniter\HTTP\IncomingRequest;

abstract class RField
{
    protected string $fieldname;
    protected ?string $label = null;
    protected ?string $rules = null;
    protected mixed $value = null;
    protected string $type = 'text';
    protected ?string $query = null;
    protected array $options = [];
    protected mixed $default = null;
    protected ?string $placeholder = null;

    public function __construct(string $fieldname)
    {
        $this->fieldname = $fieldname;
    }

    // Métodos básicos
    public function getFieldName(): string
    {
        return $this->fieldname;
    }

    public function getFieldToShow()
    {
        return $this->fieldname;
    }


    public function setFieldName(string $fieldname): void
    {
        $this->fieldname = $fieldname;
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->fieldname;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getRules(): ?string
    {
        return $this->rules;
    }

    public function setRules(string $rules): void
    {
        $this->rules = $rules;
    }

    public function getValue(): mixed
    {
        return $this->value ?? $this->default;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function setDefault(mixed $default): void
    {
        $this->default = $default;
    }

    public function getPlaceHolder(): ?string
    {
        return $this->placeholder;
    }

    public function setPlaceHolder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    // Carga de propiedades desde un array
    public function loadFromArray(array $array): void
    {
        $properties = ['label', 'value', 'rules', 'type', 'options', 'default', 'query', 'placeholder'];
        foreach ($properties as $prop) {
            $method = "set" . ucfirst($prop);
            if (array_key_exists($prop, $array) && method_exists($this, $method)) {
                $this->{$method}($array[$prop]);
            }
        }
    }

    // Cargar valores por defecto
    public function setDefaults(): void
    {
        $this->label       = $this->label ?? $this->getFieldName();
        $this->rules       = $this->rules ?? '';
        $this->value       = $this->value ?? '';
        $this->type        = $this->type ?? 'text';
        $this->options     = $this->options ?? [];
        $this->default     = $this->default ?? null;
        $this->placeholder = $this->placeholder ?? '';
    }

    // Atributos extra para controles HTML
    protected function extraAttributesForControl(): string
    {
        $rules      = explode('|', $this->rules ?? '');
        $attributes = [];

        $map = [
            'required' => 'required',
            'readonly' => 'readonly',
            'disabled' => 'disabled readonly',
            'money'    => 'money'
        ];

        foreach ($map as $rule => $attribute) {
            if (in_array($rule, $rules)) {
                $attributes[] = $attribute;
            }
        }

        return implode(' ', $attributes);
    }

    public function isRequired(): bool
    {
        $rules = explode('|', $this->rules ?? '');
        return in_array('required', $rules);
    }

    // Cargar las variables del campo en un array
    public function loadVars(): array
    {
        return [
            'name'             => $this->getFieldName(),
            'value'            => $this->getValue(),
            'label'            => $this->getLabel(),
            'type'             => $this->getType(),
            'default'          => $this->getDefault(),
            'options'          => $this->getOptions(),
            'placeholder'      => $this->getPlaceHolder(),
            'extra_attributes' => $this->extraAttributesForControl(),
        ];
    }

    // Construcción del control HTML
    public function constructControl(): string
    {
        $data     = $this->loadVars();
        $viewPath = APPPATH . 'ThirdParty/Ragnos/Views/rfield/' . $this->type . 'field.php';

        if (file_exists($viewPath)) {
            return view('App\ThirdParty\Ragnos\Views\rfield/' . $this->type . 'field', $data);
        }

        return view('App\ThirdParty\Ragnos\Views\rfield/simpletextfield', $data);
    }

    // Obtener datos del input
    public function getDataFromInput(IncomingRequest $request): mixed
    {
        return getInputValue($this->getFieldName());
    }

    // Verificar si el campo ha cambiado
    public function hasChanged(): bool
    {
        return (newValue($this->getFieldName()) !== oldValue($this->getFieldName()));
    }
}
