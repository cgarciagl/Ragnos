<?php

namespace Ragnos\Models\Fields;

enum RFieldType: string
{
    case TEXT         = 'text';
    case NUMBER       = 'number';
    case SELECT       = 'select';
    case SWITCH       = 'switch';
    case CHECKBOX     = 'checkbox';
    case DATE         = 'date';
    case DATETIME     = 'datetime';
    case TEXTAREA     = 'textarea';
    case HTMLTEXTAREA = 'htmltextarea';
    case MONEY        = 'money';
    case IMAGE        = 'image';
    case FILE         = 'file';
    case PILLBOX      = 'pillbox';
    case SEARCH       = 'search';
    case HIDDEN       = 'hidden';

    /**
     * Tipo de campo que Ragnos usa internamente para dibujar el control.
     * Algunos casos conservan un valor histórico distinto (por ejemplo, SELECT = 'select'),
     * pero el campo real se construye con este tipo (por ejemplo, 'dropdown').
     */
    public function fieldType(): string
    {
        return match ($this) {
            self::SELECT   => 'dropdown',
            self::CHECKBOX => 'switch',
            self::IMAGE    => 'imageupload',
            self::FILE     => 'fileupload',
            self::MONEY    => 'text', // el formato de moneda lo aporta la regla 'money'
            default        => $this->value,
        };
    }

    public static function fromOrDefault(string|self $type, self $default = self::TEXT): self
    {
        if ($type instanceof self) {
            return $type;
        }

        $type = strtolower(trim((string) $type));

        return self::tryFrom($type) ?? self::fromFieldType($type) ?? $default;
    }

    /**
     * Busca el caso cuyo tipo interno coincide (por ejemplo, 'dropdown' => SELECT).
     */
    private static function fromFieldType(string $fieldType): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->fieldType() === $fieldType) {
                return $case;
            }
        }

        return null;
    }
}
