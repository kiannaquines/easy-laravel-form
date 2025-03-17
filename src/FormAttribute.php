<?php

namespace Formfy\EasyLaravelForm;

abstract class FormAttribute
{
    protected function formatAttributes(array $attributes): string
    {
        $formatted = '';
        foreach ($attributes as $key => $value) {
            if (!in_array($key, ['options', 'selected', 'value'])) {
                $formatted .= is_bool($value) ? ($value ? "$key " : '') : "{$key}=\"{$value}\" ";
            }
        }
        return trim($formatted);
    }
}