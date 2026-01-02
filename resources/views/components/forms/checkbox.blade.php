{{--
    Checkbox Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-forms.checkbox name="terms" label="Acepto los terminos" />
    <x-forms.checkbox name="newsletter" label="Suscribirse" color="primary" />
--}}
@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => false,
    'error' => null,
    'color' => null,
    'size' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $checkboxClasses = ['checkbox'];

    // Color
    if ($color) {
        $checkboxClasses[] = "checkbox-{$color}";
    }

    // Size
    if ($size) {
        $checkboxClasses[] = "checkbox-{$size}";
    }

    $checkboxId = $attributes->get('id', $name);
    $isChecked = old($name) !== null ? old($name) == $value : $checked;
@endphp

<fieldset class="fieldset">
    <label class="label cursor-pointer justify-start gap-3">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $checkboxId }}"
            value="{{ $value }}"
            @checked($isChecked)
            @disabled($disabled)
            @required($required)
            {{ $attributes->class($checkboxClasses) }}
        />
        @if($label)
            <span class="label-text">
                {{ $label }}
                @if($required)
                    <span class="text-error">*</span>
                @endif
            </span>
        @endif
    </label>

    @if($error)
        <p class="label text-error text-sm">{{ $error }}</p>
    @endif
</fieldset>
