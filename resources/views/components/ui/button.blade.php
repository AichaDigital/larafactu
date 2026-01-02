{{--
    Button Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.button>Default</x-ui.button>
    <x-ui.button color="primary">Primary</x-ui.button>
    <x-ui.button color="error" style="outline">Error Outline</x-ui.button>
    <x-ui.button size="sm" :loading="true">Loading</x-ui.button>
    <x-ui.button tag="a" href="/path">Link Button</x-ui.button>
--}}
@props([
    'tag' => 'button',
    'type' => 'button',
    'color' => null,
    'style' => null,
    'size' => null,
    'block' => false,
    'wide' => false,
    'square' => false,
    'circle' => false,
    'loading' => false,
    'disabled' => false,
])

@php
    $classes = ['btn'];

    // Color
    if ($color) {
        $classes[] = "btn-{$color}";
    }

    // Style
    if ($style) {
        $classes[] = "btn-{$style}";
    }

    // Size
    if ($size) {
        $classes[] = "btn-{$size}";
    }

    // Modifiers
    if ($block) $classes[] = 'btn-block';
    if ($wide) $classes[] = 'btn-wide';
    if ($square) $classes[] = 'btn-square';
    if ($circle) $classes[] = 'btn-circle';
    if ($disabled) $classes[] = 'btn-disabled';
@endphp

<{{ $tag }}
    {{ $attributes->class($classes) }}
    @if($tag === 'button') type="{{ $type }}" @endif
    @if($disabled) disabled @endif
>
    @if($loading)
        <span class="loading loading-spinner"></span>
    @endif
    {{ $slot }}
</{{ $tag }}>
