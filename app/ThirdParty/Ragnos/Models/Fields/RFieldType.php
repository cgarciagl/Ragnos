<?php

namespace App\ThirdParty\Ragnos\Models\Fields;

enum RFieldType: string
{
    case TEXT     = 'text';
    case NUMBER   = 'number';
    case SELECT   = 'select';
    case SWITCH   = 'switch';
    case CHECKBOX = 'checkbox';
    case DATE     = 'date';
    case DATETIME = 'datetime';
    case TEXTAREA = 'textarea';
    case MONEY    = 'money';
    case IMAGE    = 'image';
    case FILE     = 'file';
    case PILLBOX  = 'pillbox';
    case SEARCH   = 'search';

    public static function fromOrDefault(string|self $type, self $default = self::TEXT): self
    {
        if ($type instanceof self) {
            return $type;
        }

        return self::tryFrom(strtolower(trim((string) $type))) ?? $default;
    }
}

