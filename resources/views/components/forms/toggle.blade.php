{{--
    Toggle Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-forms.toggle name="active" label="Activo" />
    <x-forms.toggle name="notifications" label="Notificaciones" color="success" />
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
])

@php
    $toggleClasses = ['toggle'];

    // Color
    if ($color) {
        $toggleClasses[] = "toggle-{$color}";
    }

    // Size
    if ($size) {
        $toggleClasses[] = "toggle-{$size}";
    }

    $toggleId = $attributes->get('id', $name);
    $isChecked = old($name) !== null ? old($name) == $value : $checked;
@endphp

<fieldset class="fieldset">
    <label class="label cursor-pointer justify-start gap-3">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $toggleId }}"
            value="{{ $value }}"
            @checked($isChecked)
            @disabled($disabled)
            {{ $attributes->class($toggleClasses) }}
        />
        @if($label)
            <span class="label-text">{{ $label }}</span>
        @endif
    </label>

    @if($error)
        <p class="label text-error text-sm">{{ $error }}</p>
    @endif
</fieldset>
