<?php

namespace Formfy\EasyLaravelForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

abstract class DBFormBuilder extends FormAttribute
{
    protected string $method;
    protected string $action;
    protected array $fields = [];
    protected string $submitLabel = 'Submit';
    protected bool $csrf = true;
    protected array $errors = [];
    protected string $formClass = 'max-w-7xl mx-auto';
    protected string $defaultInputClass = 'w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm focus:shadow mb-2';
    protected string $submitButtonClass = 'w-full bg-blue-600 text-white p-2 rounded-md text-sm cursor-pointer mt-3';
    protected string $cancelButtonClass = 'w-full bg-red-600 text-white p-2 rounded-md text-sm cursor-pointer mt-3';
    protected ?Model $model = null;

    public function __construct($action = '', $method = 'POST', $errors = [], ?Model $model = null)
    {
        $this->action = $action;
        $this->method = strtoupper($method);
        $this->errors = $errors;
        $this->model = $model;
    }

    public function disableCsrf(): static
    {
        $this->csrf = false;
        return $this;
    }

    public function setModel(Model $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function addField(string $type, string $name, ?string $label = '', array $attributes = []): static
    {
        if ($this->model && $this->model->{$name} !== null && !isset($attributes['value'])) {
            $attributes['value'] = $this->model->{$name};
        }

        $this->fields[] = compact('type', 'name', 'label', 'attributes');
        return $this;
    }

    public function addSelectField(string $name, string $label, Model $model, string $valueField = 'id', string $textField = 'name', ?array $attributes = []): static
    {
        $options = $model::pluck($textField, $valueField)->toArray();
        $attributes['options'] = $options;

        if ($this->model && isset($this->model->{$name})) {
            $attributes['selected'] = $this->model->{$name};
        }

        return $this->addField('select', $name, $label, $attributes);
    }

    public function setSubmitLabel(string $label): static
    {
        $this->submitLabel = $label;
        return $this;
    }

    public function setFormClass(string $class): static
    {
        $this->formClass = $class;
        return $this;
    }

    public function setSubmitButtonClass(string $class): static
    {
        $this->submitButtonClass = $class;
        return $this;
    }

    public function setInputClass(string $class): static
    {
        $this->defaultInputClass = $class;
        return $this;
    }

    public function setBaseMethod(string $method): string
    {
        return match ($method) {
            'PUT', 'DELETE', 'PATCH', 'POST' => 'POST',
            default => 'GET',
        };
    }

    public function render(): string
    {
        $form = "<form action='{$this->action}' method='{$this->setBaseMethod($this->method)}' class='{$this->formClass}'>";

        if ($this->csrf && $this->method !== 'GET') {
            $form .= csrf_field();
            $form .= method_field($this->method);
        }

        foreach ($this->fields as $field) {
            $form .= "<div class='mb-4'>";

            if (!empty($field['label'])) {
                $form .= "<label class='text-gray-500 text-sm' for='{$field['name']}'>{$field['label']}</label>";
            }

            $attrs = $this->formatAttributes($field['attributes']);
            $errorClass = isset($this->errors[$field['name']]) ? 'border-red-500' : '';

            $fieldClass = $field['attributes']['class'] ?? $this->defaultInputClass;

            switch ($field['type']) {
                case 'textarea':
                    $value = $field['attributes']['value'] ?? '';
                    $form .= "<textarea name='{$field['name']}' id='{$field['name']}' class='{$fieldClass} {$errorClass}' {$attrs}>{$value}</textarea>";
                    break;
                case 'select':
                    $form .= "<select name='{$field['name']}' id='{$field['name']}' class='{$fieldClass} {$errorClass}' {$attrs}>";
                    foreach ($field['attributes']['options'] as $value => $option) {
                        $selected = ($field['attributes']['selected'] ?? '') == $value ? "selected" : "";
                        $form .= "<option value='{$value}' {$selected}>{$option}</option>";
                    }
                    $form .= "</select>";
                    break;
                case 'checkbox':
                    $checked = isset($field['attributes']['checked']) ? "checked" : "";
                    $form .= "<input type='checkbox' name='{$field['name']}' id='{$field['name']}' class='{$fieldClass}' {$attrs} {$checked}>";
                    $form .= "<label for='{$field['name']}'>{$field['label']}</label>";
                    break;
                default:
                    $value = $field['attributes']['value'] ?? '';
                    $form .= "<input type='{$field['type']}' name='{$field['name']}' id='{$field['name']}' value='{$value}' class='{$fieldClass} {$errorClass}' {$attrs}>";
                    break;
            }

            if (isset($this->errors[$field['name']])) {
                foreach ($this->errors[$field['name']] as $error) {
                    $form .= "<p class='text-red-500 text-xs mt-1'>{$error}</p>";
                }
            }

            $form .= "</div>";
        }

        $form .= "<input type='submit' class='{$this->submitButtonClass}' value='{$this->submitLabel}'/>";
        $form .= "<input type='button' class='{$this->cancelButtonClass}' value='Back' onclick='window.history.back(); return false;'/>";
        $form .= "</form>";

        return new HtmlString($form);
    }
}
