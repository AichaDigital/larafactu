{{--
    Textarea Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-forms.textarea name="description" label="Descripcion" />
    <x-forms.textarea name="notes" rows="5" :error="$errors->first('notes')" />
--}}
@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => '',
    'rows' => 3,
    'error' => null,
    'hint' => null,
    'color' => null,
    'size' => null,
    'disabled' => false,
    'readonly' => false,
    'required' => false,
])

@php
    $textareaClasses = ['textarea', 'w-full'];

    // Error state
    if ($error) {
        $textareaClasses[] = 'textarea-error';
    } elseif ($color) {
        $textareaClasses[] = "textarea-{$color}";
    }

    // Size
    if ($size) {
        $textareaClasses[] = "textarea-{$size}";
    }

    $textareaId = $attributes->get('id', $name);
    $textareaValue = old($name, $value);
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

    <textarea
        name="{{ $name }}"
        id="{{ $textareaId }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @disabled($disabled)
        @readonly($readonly)
        @required($required)
        {{ $attributes->class($textareaClasses) }}
    >{{ $textareaValue }}</textarea>

    @if($error)
        <p class="label text-error text-sm">{{ $error }}</p>
    @elseif($hint)
        <p class="label text-base-content/60 text-sm">{{ $hint }}</p>
    @endif
</fieldset>
