{{--
    Input Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-forms.input name="email" label="Email" type="email" />
    <x-forms.input name="name" label="Nombre" :error="$errors->first('name')" />
    <x-forms.input name="search" placeholder="Buscar..." icon="search" />
--}}
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'placeholder' => '',
    'value' => null,
    'error' => null,
    'hint' => null,
    'color' => null,
    'size' => null,
    'icon' => null,
    'iconPosition' => 'start',
    'disabled' => false,
    'readonly' => false,
    'required' => false,
])

@php
    $inputClasses = ['input', 'w-full'];

    // Error state
    if ($error) {
        $inputClasses[] = 'input-error';
    } elseif ($color) {
        $inputClasses[] = "input-{$color}";
    }

    // Size
    if ($size) {
        $inputClasses[] = "input-{$size}";
    }

    $inputId = $attributes->get('id', $name);
    $inputValue = old($name, $value);
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

    @if($icon)
        <label class="input {{ implode(' ', $inputClasses) }}">
            @if($iconPosition === 'start')
                <span class="text-base-content/50">
                    @include("components.forms.icons.{$icon}")
                </span>
            @endif

            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $inputId }}"
                value="{{ $inputValue }}"
                placeholder="{{ $placeholder }}"
                @disabled($disabled)
                @readonly($readonly)
                @required($required)
                {{ $attributes->except(['class', 'id']) }}
                class="grow"
            />

            @if($iconPosition === 'end')
                <span class="text-base-content/50">
                    @include("components.forms.icons.{$icon}")
                </span>
            @endif
        </label>
    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $inputValue }}"
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            @readonly($readonly)
            @required($required)
            {{ $attributes->class($inputClasses) }}
        />
    @endif

    @if($error)
        <p class="label text-error text-sm">{{ $error }}</p>
    @elseif($hint)
        <p class="label text-base-content/60 text-sm">{{ $hint }}</p>
    @endif
</fieldset>
