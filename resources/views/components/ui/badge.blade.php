{{--
    Badge Component - DaisyUI wrapper
    ADR-005: Semantic DaisyUI classes

    Usage:
    <x-ui.badge>Default</x-ui.badge>
    <x-ui.badge color="primary">Primary</x-ui.badge>
    <x-ui.badge color="success" style="outline" size="lg">Large Success</x-ui.badge>
--}}
@props([
    'color' => null,
    'style' => null,
    'size' => null,
])

@php
    $classes = ['badge'];

    // Color
    if ($color) {
        $classes[] = "badge-{$color}";
    }

    // Style
    if ($style) {
        $classes[] = "badge-{$style}";
    }

    // Size
    if ($size) {
        $classes[] = "badge-{$size}";
    }
@endphp

<span {{ $attributes->class($classes) }}>{{ $slot }}</span>
