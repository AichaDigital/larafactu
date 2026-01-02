{{--
    Select Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-forms.select name="country" label="Pais" :options="$countries" />
    <x-forms.select name="status" :options="['active' => 'Activo', 'inactive' => 'Inactivo']" />
--}}
@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'error' => null,
    'hint' => null,
    'color' => null,
    'size' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $selectClasses = ['select', 'w-full'];

    // Error state
    if ($error) {
        $selectClasses[] = 'select-error';
    } elseif ($color) {
        $selectClasses[] = "select-{$color}";
    }

    // Size
    if ($size) {
        $selectClasses[] = "select-{$size}";
    }

    $selectId = $attributes->get('id', $name);
    $selectValue = old($name, $value);
@endphp

<fieldset class="fieldset">
    @if($label)
        <legend class="fieldset-legend">
            {{ $label }}
            @if($required)
                <span class="text-error">*</span>
            @endif
        </legend>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        @disabled($disabled)
        @required($required)
        {{ $attributes->class($selectClasses) }}
    >
        @if($placeholder)
            <option value="" disabled {{ !$selectValue ? 'selected' : '' }}>{{ $placeholder }}</option>
        @endif

        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ $selectValue == $optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if($error)
        <p class="label text-error text-sm">{{ $error }}</p>
    @elseif($hint)
        <p class="label text-base-content/60 text-sm">{{ $hint }}</p>
    @endif
</fieldset>
