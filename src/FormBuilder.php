<?php

namespace Kian\EasyLaravelForm;

use Illuminate\Support\HtmlString;

class FormBuilder extends FormAttribute
{
    protected string $method;
    protected string $action;
    protected array $inputs = [];
    protected bool $csrf = true;
    protected array $errors = [];
    protected string $defaultInputClass = 'w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500';

    public function __construct(string $action = '', string $method = 'POST', array $errors = [])
    {
        $this->action = $action;
        $this->method = strtoupper($method);
        $this->errors = $errors;
    }

    public function disableCsrf(): static
    {
        $this->csrf = false;
        return $this;
    }

    public function addField(string $type, string $name, ?string $label = '', array $attributes = []): static
    {
        $this->inputs[] = compact('type', 'name', 'label', 'attributes');
        return $this;
    }

    public function addSelectField(string $name, string $label, array $options, array $attributes = []): static
    {
        $attributes['options'] = $options;
        return $this->addField('select', $name, $label, $attributes);
    }

    public function setInputClass(string $class): static
    {
        $this->defaultInputClass = $class;
        return $this;
    }

    public function render(): HtmlString
    {
        $form = "<form action=\"{$this->action}\" method=\"" . ($this->method === 'GET' ? 'GET' : 'POST') . "\">";

        if ($this->csrf && $this->method !== 'GET') {
            $form .= csrf_field();
            if (!in_array($this->method, ['GET', 'POST'])) {
                $form .= method_field($this->method);
            }
        }

        foreach ($this->inputs as $input) {
            $form .= "<div class='mb-4'>";

            if (!empty($input['label']) && $input['type'] !== 'checkbox') {
                $form .= "<label class='text-gray-500 text-sm' for='{$input['name']}'>{$input['label']}</label>";
            }

            $attrs = $this->formatAttributes($input['attributes']);
            $errorClass = isset($this->errors[$input['name']]) ? 'border-red-500' : '';

            // Determine class per input (allow override)
            $fieldClass = $input['attributes']['class'] ?? $this->defaultInputClass;

            switch ($input['type']) {
                case 'textarea':
                    $value = $input['attributes']['value'] ?? '';
                    $form .= "<textarea name='{$input['name']}' id='{$input['name']}' class='{$fieldClass} {$errorClass}' {$attrs}>{$value}</textarea>";
                    break;
                case 'select':
                    $form .= "<select name='{$input['name']}' id='{$input['name']}' class='{$fieldClass} {$errorClass}' {$attrs}>";
                    foreach ($input['attributes']['options'] as $value => $option) {
                        $selected = ($input['attributes']['selected'] ?? '') == $value ? "selected" : "";
                        $form .= "<option value='{$value}' {$selected}>{$option}</option>";
                    }
                    $form .= "</select>";
                    break;
                case 'checkbox':
                    $checked = isset($input['attributes']['checked']) && $input['attributes']['checked'] ? "checked" : "";
                    $form .= "<div class='flex items-center'><input type='checkbox' name='{$input['name']}' id='{$input['name']}' class='{$fieldClass}' {$attrs} {$checked}>";
                    $form .= "<label class='ml-2 text-gray-500 text-sm' for='{$input['name']}'>{$input['label']}</label></div>";
                    break;
                default:
                    $value = $input['attributes']['value'] ?? '';
                    $form .= "<input type='{$input['type']}' name='{$input['name']}' id='{$input['name']}' value='{$value}' class='{$fieldClass} {$errorClass}' {$attrs}>";
                    break;
            }

            if (isset($this->errors[$input['name']])) {
                foreach ($this->errors[$input['name']] as $error) {
                    $form .= "<p class='text-red-500 text-xs mt-1'>{$error}</p>";
                }
            }

            $form .= "</div>";
        }

        $form .= "</form>";

        return new HtmlString($form);
    }
}